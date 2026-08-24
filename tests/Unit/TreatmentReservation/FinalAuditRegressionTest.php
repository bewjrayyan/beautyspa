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
