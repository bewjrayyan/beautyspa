<?php

namespace Tests\Unit\TreatmentReservation;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Beautician\Entities\Beautician;
use Modules\GoogleIntegration\Services\GoogleCalendarService;
use Modules\GoogleIntegration\Services\GoogleSheetsService;
use Modules\GoogleIntegration\Services\GoogleSheetsSyncAlertNotifier;
use Modules\GoogleIntegration\Services\GoogleSheetsSyncLogger;
use Modules\GoogleIntegration\Services\OrderGoogleSyncService;
use Modules\GoogleIntegration\Support\GoogleSheetsCellSanitizer;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Http\Middleware\BeauticianPortalPermissionMiddleware;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityService;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;
use Modules\TreatmentReservation\Services\BookingSelfService;
use Modules\TreatmentReservation\Services\TreatmentBookingActivityLogger;
use Modules\User\Entities\User;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;

class FinalAuditRegressionTest extends TestCase
{
    #[Test]
    public function admin_portal_account_resolves_the_selected_beautician_user(): void
    {
        $root = dirname(__DIR__, 3);
        $controller = file_get_contents($root . '/modules/TreatmentReservation/Http/Controllers/Admin/PortalAccountController.php');
        $request = file_get_contents($root . '/modules/TreatmentReservation/Http/Requests/UpdatePortalProfileRequest.php');

        $this->assertStringContainsString('$this->portalUser($beautician)->update([', $controller);
        $this->assertStringContainsString('$this->portalUser($beautician),', $controller);
        $this->assertStringContainsString("\$userId = \$beautician instanceof Beautician", $request);
        $this->assertStringContainsString("Rule::unique('users', 'email')->ignore(\$userId)", $request);
        $this->assertStringNotContainsString('auth()->user()->update([', $controller);
    }

    #[Test]
    public function non_owner_portal_payload_removes_pii_notes_activity_and_actions(): void
    {
        $payload = TreatmentBooking::applyPortalViewerScope([
            'beautician_id' => 10,
            'customer_phone' => '60123456789',
            'customer_email' => 'private@example.test',
            'notes' => 'private order note',
            'beautician_notes' => 'private treatment note',
            'beautician_checklist' => [['label' => 'Sensitive task']],
            'recent_activities' => [['to_value' => 'Sensitive activity']],
            'can_whatsapp_customer' => true,
            'can_reschedule' => true,
        ], 99);

        $this->assertNull($payload['customer_phone']);
        $this->assertNull($payload['customer_email']);
        $this->assertNull($payload['notes']);
        $this->assertNull($payload['beautician_notes']);
        $this->assertSame([], $payload['beautician_checklist']);
        $this->assertSame([], $payload['recent_activities']);
        $this->assertFalse($payload['can_whatsapp_customer']);
        $this->assertFalse($payload['can_reschedule']);
    }

    #[Test]
    public function spreadsheet_cells_neutralize_formula_prefixes_after_whitespace(): void
    {
        foreach (['=1+1', '+SUM(A1:A2)', '-2+3', '@IMPORTXML("x")', "\t=cmd"] as $value) {
            $this->assertStringStartsWith("'", GoogleSheetsCellSanitizer::sanitize($value));
        }

        $this->assertSame('Normal customer', GoogleSheetsCellSanitizer::sanitize('Normal customer'));
        $this->assertSame(125.50, GoogleSheetsCellSanitizer::sanitize(125.50));
    }

    #[Test]
    public function calendar_sync_creates_one_event_for_each_scheduled_treatment(): void
    {
        $order = $this->getMockBuilder(Order::class)->onlyMethods(['save'])->getMock();
        $order->setRawAttributes(['id' => 1394, 'status' => Order::COMPLETED, 'google_calendar_event_id' => null], true);
        $order->expects($this->once())->method('save')->willReturn(true);

        $first = $this->scheduledBooking(501, '2026-08-28', '14:00');
        $second = $this->scheduledBooking(502, '2026-08-29', '16:00');

        $calendar = $this->createMock(GoogleCalendarService::class);
        $calendar->expects($this->exactly(2))
            ->method('createTreatmentAppointmentEvent')
            ->willReturnCallback(function (Order $actualOrder, TreatmentBooking $booking) use ($order, $first): string {
                $this->assertSame($order, $actualOrder);

                return $booking === $first ? 'event-treatment-1' : 'event-treatment-2';
            });

        $service = new OrderGoogleSyncService(
            $this->createStub(GoogleSheetsService::class),
            $calendar,
            $this->createStub(GoogleSheetsSyncLogger::class),
            $this->createStub(GoogleSheetsSyncAlertNotifier::class),
        );

        $method = new ReflectionMethod($service, 'syncTreatmentAppointments');
        $result = $method->invoke($service, $order, new Collection([$first, $second]), false);

        $this->assertTrue($result['ok']);
        $this->assertTrue($result['created']);
        $this->assertSame('event-treatment-1', $first->google_calendar_event_id);
        $this->assertSame('event-treatment-2', $second->google_calendar_event_id);
        $this->assertSame('event-treatment-1', $order->google_calendar_event_id);
    }

    #[Test]
    public function availability_lookup_is_capped_to_ninety_three_days(): void
    {
        $activity = $this->createStub(TreatmentBookingActivityLogger::class);
        $appointments = $this->createStub(AppointmentAvailabilityService::class);
        $beautician = $this->createMock(BeauticianAvailabilityService::class);
        $beautician->expects($this->exactly(94))->method('availableSlots')->willReturn([]);
        $service = new BookingSelfService($activity, $appointments, $beautician);
        $booking = new TreatmentBooking();
        $booking->setRawAttributes(['id' => 55, 'beautician_id' => 7], true);
        $booking->setRelation('order', null);

        $dates = $service->availableDatesForBooking($booking, '2026-01-01', '2027-01-01');

        $this->assertSame([], $dates);
    }

    #[Test]
    public function calendar_summary_excludes_expensive_drawer_only_fields(): void
    {
        $booking = new TreatmentBooking();
        $booking->setRawAttributes([
            'id' => 498,
            'status' => TreatmentBooking::STATUS_PENDING,
            'customer_first_name' => 'Calendar',
            'customer_last_name' => 'Customer',
            'appointment_date' => '2026-08-29',
            'appointment_time' => '16:00',
            'duration_minutes_snapshot' => 180,
            'beautician_notes' => 'Drawer only',
        ], true);
        $booking->setRelation('product', null);
        $booking->setRelation('beautician', null);

        $payload = $booking->toCalendarSummaryPayload();

        $this->assertFalse($payload['details_loaded']);
        $this->assertSame('2026-08-29', $payload['date']);
        $this->assertSame(180, $payload['slot_duration_minutes']);
        $this->assertSame('7:00 PM', $payload['appointment_end_time']);
        $this->assertArrayNotHasKey('beautician_notes', $payload);
        $this->assertArrayNotHasKey('recent_activities', $payload);
        $this->assertArrayNotHasKey('payment_status_label', $payload);
    }


    #[Test]
    public function calendar_uses_local_dates_and_uniform_month_week_views(): void
    {
        $root = dirname(__DIR__, 3);
        $js = file_get_contents($root . '/modules/TreatmentReservation/Resources/assets/admin/js/main.js');
        $blade = file_get_contents($root . '/modules/TreatmentReservation/Resources/views/admin/reservations/partials/calendar.blade.php');
        $entity = file_get_contents($root . '/modules/TreatmentReservation/Entities/TreatmentBooking.php');
        $controller = file_get_contents($root . '/modules/TreatmentReservation/Http/Controllers/Admin/ReservationController.php');
        $en = file_get_contents($root . '/modules/TreatmentReservation/Resources/lang/en/admin.php');
        $ms = file_get_contents($root . '/modules/TreatmentReservation/Resources/lang/ms/admin.php');

        $this->assertStringContainsString('static localDateKey', $js);
        $this->assertStringContainsString('static parseLocalDate', $js);
        $this->assertStringContainsString('isWeekCalView()', $js);
        $this->assertStringNotContainsString('toISOString().slice(0, 10)', $js);
        $this->assertStringContainsString('spa_branch_id', $js);
        $this->assertStringContainsString('appointment_time_value', $js);
        $this->assertStringContainsString('data-cal-view="week"', $blade);
        $this->assertStringContainsString('id="tr-cal-day-view"', $blade);
        $this->assertStringContainsString('tr-calendar-view-toggle', $blade);
        $this->assertStringContainsString('?int $spaBranchId = null', $entity);
        $this->assertStringContainsString("'spa_branch_id' => ['nullable', 'integer']", $controller);
        $this->assertStringContainsString("'view_week'", $en);
        $this->assertStringContainsString("'view_week'", $ms);
    }

    #[Test]
    public function calendar_navigation_keeps_month_data_cached_and_loads_drawer_details_on_demand(): void
    {
        $root = dirname(__DIR__, 3);
        $calendar = file_get_contents($root . '/modules/TreatmentReservation/Resources/assets/admin/js/main.js');
        $dashboard = file_get_contents($root . '/modules/TreatmentReservation/Resources/assets/admin/js/dashboard.js');
        $preview = file_get_contents($root . '/modules/TreatmentReservation/Resources/assets/admin/js/kanban-helpers.js');
        $controller = file_get_contents($root . '/modules/TreatmentReservation/Http/Controllers/Admin/ReservationController.php');

        $this->assertStringContainsString('this.calendarDataCache = new Map()', $calendar);
        $this->assertStringContainsString('this.prefetchAdjacentMonths(requestedMonth)', $calendar);
        $this->assertStringContainsString('Promise.all([', $calendar);
        $this->assertStringContainsString('datesCache: new Map()', $dashboard);
        $this->assertStringContainsString('prefetchRescheduleMonths(state)', $dashboard);
        $this->assertStringContainsString('cached.details_loaded === false', $preview);
        $this->assertStringContainsString('detailsUrlTemplate.replace("__ID__", key)', $preview);
        $this->assertStringContainsString('toCalendarSummaryPayload()', $controller);
        $this->assertStringContainsString('withCalendarDetails()', $controller);
    }

    #[Test]
    public function beautician_self_access_bypasses_admin_permission_but_other_profiles_require_it(): void
    {
        $middleware = new BeauticianPortalPermissionMiddleware();
        $beautician = new Beautician(['user_id' => 44]);
        $user = $this->getMockBuilder(User::class)->onlyMethods(['hasAccess'])->getMock();
        $user->setAttribute('id', 44);
        $user->expects($this->never())->method('hasAccess');
        $request = Request::create('/admin/beauticians/1/portal');
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('portal_beautician', $beautician);

        $response = $middleware->handle(
            $request,
            fn (): Response => new Response('ok'),
            'admin.treatment_reservations.edit',
        );

        $this->assertSame('ok', $response->getContent());
    }

    #[Test]
    public function beautician_operational_views_and_customer_lookup_are_scoped_to_ownership(): void
    {
        $root = dirname(__DIR__, 3);
        $portal = file_get_contents($root . '/modules/TreatmentReservation/Http/Controllers/Admin/PortalController.php');
        $admin = file_get_contents($root . '/modules/TreatmentReservation/Http/Controllers/Admin/ReservationController.php');
        $analytics = file_get_contents($root . '/modules/TreatmentReservation/Services/TreatmentReservationAnalyticsService.php');

        $this->assertStringContainsString("'stats' => \$this->dashboard->stats(\$beauticianId, \$categoryId, \$spaBranchId)", $portal);
        $this->assertStringContainsString("->forCalendar(\$request->input('month'), \$viewerBeauticianId)", $portal);
        $this->assertStringContainsString("->forKanban(\$viewerBeauticianId, \$request->integer('treatment_category_id') ?: null)", $portal);
        $this->assertStringContainsString("'booking_id' => ['required', 'integer']", $portal);

        $calendarEventMethod = substr(
            $portal,
            strpos($portal, 'public function calendarEvent(Request'),
            strpos($portal, 'public function kanbanBoard') - strpos($portal, 'public function calendarEvent(Request'),
        );
        $this->assertStringContainsString("->where('beautician_id', \$beautician->id)", $calendarEventMethod);

        $customerProfileMethod = substr(
            $portal,
            strpos($portal, 'public function customerProfile'),
            strpos($portal, 'public function sendCustomerReminder') - strpos($portal, 'public function customerProfile'),
        );
        $this->assertStringContainsString("->where('beautician_id', \$beautician->id)", $customerProfileMethod);
        $this->assertStringNotContainsString('forPhone(', $customerProfileMethod);

        $this->assertStringContainsString("'analytics' => null", $admin);
        $this->assertStringContainsString("'analyticsCharts' => null", $admin);
        $this->assertStringContainsString("'revenueByBeautician' => \$this->revenueByBeautician(\$days, 5, \$beauticianId)", $analytics);
        $this->assertStringContainsString("->whereDate('appointment_date', '>=', \$from)", $analytics);
    }

    #[Test]
    public function race_and_otp_error_guards_remain_in_the_controller_and_locked_service_paths(): void
    {
        $root = dirname(__DIR__, 3);
        $service = file_get_contents($root . '/modules/TreatmentReservation/Services/BookingSelfService.php');
        $controller = file_get_contents($root . '/modules/TreatmentReservation/Http/Controllers/BookingSelfServiceController.php');
        $routes = file_get_contents($root . '/modules/Beautician/Routes/admin.php');
        $portalAccess = file_get_contents($root . '/modules/TreatmentReservation/Http/Middleware/BeauticianPortalAccessMiddleware.php');

        $this->assertGreaterThanOrEqual(2, substr_count($service, '$this->assertSelfServiceMutable($lockedBooking);'));
        $this->assertStringContainsString("trans('treatmentreservation::public.otp_send_failed')", $controller);
        $this->assertStringContainsString("trans('treatmentreservation::public.otp_verification_failed')", $controller);
        $this->assertStringNotContainsString("['message' => \$exception->getMessage()]", substr($controller, 0, strpos($controller, 'public function cancel')));
        $this->assertStringContainsString('beautician.portal.permission:admin.treatment_reservations.edit', $routes);
        $this->assertStringContainsString('beautician.portal.permission:admin.beauticians.edit', $routes);
        $this->assertStringNotContainsString("hasAccess('admin.beauticians.edit')", $portalAccess);
        $this->assertStringContainsString('if (! $user->isBeauticianOnly())', $portalAccess);
    }



    #[Test]
    public function crm_dashboard_payload_skips_unused_heavy_sections_and_ledger_avoids_kanban_enrich(): void
    {
        $root = dirname(__DIR__, 3);
        $service = file_get_contents($root . '/modules/TreatmentReservation/Services/ReservationDashboardService.php');
        $en = file_get_contents($root . '/modules/TreatmentReservation/Resources/lang/en/admin.php');
        $ms = file_get_contents($root . '/modules/TreatmentReservation/Resources/lang/ms/admin.php');

        $this->assertStringContainsString("'upcomingBookings' => []", $service);
        $this->assertStringContainsString("'beauticianWorkload' => []", $service);
        $this->assertStringContainsString("'recentActivity' => []", $service);
        $this->assertStringContainsString("'ledger' => []", $service);
        $this->assertStringContainsString("'ledgerCount' => 0", $service);
        $this->assertStringContainsString('Lightweight list row', $service);
        $this->assertStringNotContainsString("toKanbanPayload()", substr(
            $service,
            strpos($service, 'private function serializeLedgerRow'),
            strpos($service, 'private function ledgerStatusLabel') - strpos($service, 'private function serializeLedgerRow')
        ));
        $this->assertStringContainsString('Overdue appointments, missing specialists', $en);
        $this->assertStringNotContainsString("'needs_attention_lead' => 'Overdue, starting soon, or still pending'", $en);
        $this->assertStringContainsString('Temujanji tertunggak, tiada pakar', $ms);
        $this->assertStringNotContainsString("'needs_attention_lead' => 'Lewat, akan bermula, atau masih pending'", $ms);
    }

    #[Test]
    public function needs_attention_payment_uses_order_status_and_total_is_unique(): void
    {
        $root = dirname(__DIR__, 3);
        $needs = file_get_contents($root . '/modules/TreatmentReservation/Services/CrmNeedsAttentionService.php');

        $this->assertStringContainsString("whereHas('order'", $needs);
        $this->assertStringContainsString('Order::PAYMENT_PAID', $needs);
        $this->assertStringContainsString("where('spa_branch_id', \$spaBranchId)", $needs);
        $this->assertStringContainsString('REMINDER_HORIZON_DAYS', $needs);
        $this->assertStringContainsString('REMINDER_HORIZON_DAYS - 1', $needs);
        $this->assertStringContainsString('SCHEDULE_STATUS_TBA', $needs);
        $this->assertStringContainsString('member_ids', $needs);
        $this->assertStringContainsString("unset(\$bucket['member_ids'])", $needs);
    }

    #[Test]
    public function crm_dashboard_uses_needs_attention_panel_instead_of_booking_stats(): void
    {
        $root = dirname(__DIR__, 3);
        $dashboard = file_get_contents($root . '/modules/TreatmentReservation/Resources/views/admin/reservations/partials/dashboard.blade.php');
        $service = file_get_contents($root . '/modules/TreatmentReservation/Services/ReservationDashboardService.php');
        $panel = file_get_contents($root . '/modules/TreatmentReservation/Resources/views/admin/reservations/partials/dashboard/needs-attention-panel.blade.php');
        $needs = file_get_contents($root . '/modules/TreatmentReservation/Services/CrmNeedsAttentionService.php');
        $en = file_get_contents($root . '/modules/TreatmentReservation/Resources/lang/en/admin.php');
        $ms = file_get_contents($root . '/modules/TreatmentReservation/Resources/lang/ms/admin.php');

        $this->assertStringContainsString('needs-attention-panel', $dashboard);
        $this->assertStringNotContainsString('booking-stats-panel', $dashboard);
        $this->assertStringNotContainsString('ledger-table', $dashboard);
        $this->assertStringContainsString('CrmNeedsAttentionService', $service);
        $this->assertStringContainsString("bucket('overdue'", $needs);
        $this->assertStringContainsString('needs_attention_bucket_', $needs);
        $this->assertStringContainsString('tr-crm-needs__item', $panel);
        $this->assertStringContainsString("'needs_attention_title'", $en);
        $this->assertStringContainsString("'needs_attention_title'", $ms);
    }

    #[Test]
    public function calendar_includes_completed_bookings_even_when_order_is_gone(): void
    {
        $root = dirname(__DIR__, 3);
        $booking = file_get_contents($root . '/modules/TreatmentReservation/Entities/TreatmentBooking.php');
        $scope = substr(
            $booking,
            strpos($booking, 'public function scopeForCalendar'),
            strpos($booking, 'public function scopeWithCalendarDetails') - strpos($booking, 'public function scopeForCalendar'),
        );

        $this->assertStringContainsString('function scopeVisibleOnCalendar', $booking);
        $this->assertStringContainsString('->visibleOnCalendar()', $scope);
        $this->assertStringNotContainsString('->withActiveOrder()', $scope);
        $this->assertStringContainsString("->withTrashed()", $booking);
        $this->assertStringContainsString("orWhere('status', self::STATUS_COMPLETED)", $booking);
        $this->assertStringContainsString('SOURCE_ADMIN_MANUAL', $booking);
        $this->assertStringContainsString('withTreatmentProduct()', $scope);

        $admin = file_get_contents($root . '/modules/TreatmentReservation/Http/Controllers/Admin/ReservationController.php');
        $portal = file_get_contents($root . '/modules/TreatmentReservation/Http/Controllers/Admin/PortalController.php');
        $profile = file_get_contents($root . '/modules/TreatmentReservation/Services/CustomerCrmProfileService.php');
        $this->assertStringContainsString('->visibleOnCalendar()', $admin);
        $this->assertStringContainsString('->visibleOnCalendar()', $portal);
        $this->assertStringContainsString('forBooking($booking, (int) $beautician->id)', $portal);
        $this->assertStringContainsString('?int $viewerBeauticianId = null', $profile);
    }

    #[Test]
    public function ledger_hides_orphan_checkout_bookings_and_checkout_delete_trashes_linked_rows(): void
    {
        $root = dirname(__DIR__, 3);
        $booking = file_get_contents($root . '/modules/TreatmentReservation/Entities/TreatmentBooking.php');
        $sync = file_get_contents($root . '/modules/TreatmentReservation/Services/BookingSyncService.php');
        $dashboard = file_get_contents($root . '/modules/TreatmentReservation/Services/ReservationDashboardService.php');
        $orderService = file_get_contents($root . '/modules/Checkout/Services/OrderService.php');
        $crmDashboard = file_get_contents($root . '/modules/TreatmentReservation/Resources/views/admin/reservations/partials/dashboard.blade.php');

        $scope = substr(
            $booking,
            strpos($booking, 'public function scopeWithActiveOrder'),
            strpos($booking, 'public function scopeWithTreatmentProduct') - strpos($booking, 'public function scopeWithActiveOrder'),
        );
        $this->assertStringContainsString("whereNotNull('order_id')->whereHas('order')", $scope);
        $this->assertStringContainsString('SOURCE_ADMIN_MANUAL', $scope);
        $this->assertStringContainsString('SOURCE_PORTAL_MANUAL', $scope);
        $this->assertStringNotContainsString("\$inner->whereNull('order_id')\n                ->orWhereHas('order')", $scope);

        $this->assertStringContainsString("->where('source', TreatmentBooking::SOURCE_CHECKOUT)", $sync);
        $this->assertStringContainsString('->trashBookingsForOrder($order)', $orderService);
        $this->assertGreaterThanOrEqual(2, substr_count($dashboard, '->withActiveOrder()'));
        $this->assertStringNotContainsString('ledger-table', $crmDashboard);
    }

    private function scheduledBooking(int $id, string $date, string $time): TreatmentBooking
    {
        $booking = $this->getMockBuilder(TreatmentBooking::class)->onlyMethods(['save'])->getMock();
        $booking->setRawAttributes([
            'id' => $id,
            'status' => TreatmentBooking::STATUS_PENDING,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'google_calendar_event_id' => null,
        ], true);
        $booking->expects($this->once())->method('save')->willReturn(true);

        return $booking;
    }
}
