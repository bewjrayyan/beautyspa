<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Beautician\Entities\Beautician;
use Modules\Product\Entities\Product;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Entities\TreatmentCategory;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;
use Modules\TreatmentReservation\Services\CustomerAppointmentReminderService;
use Modules\TreatmentReservation\Services\CustomerCrmProfileService;
use Modules\TreatmentReservation\Services\ReservationDashboardService;
use Modules\TreatmentReservation\Services\TreatmentBookingActivityLogger;
use Modules\TreatmentReservation\Services\BookingCustomerWhatsAppService;
use Modules\TreatmentReservation\Services\BookingJobSheetOrderSync;
use Modules\TreatmentReservation\Services\ManualBookingProductCatalogService;
use Modules\TreatmentReservation\Services\TreatmentBookingsReportService;
use Modules\TreatmentReservation\Services\TreatmentReservationAnalyticsService;
use Modules\TreatmentReservation\Services\UpcomingJobUrgencyService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationController extends Controller
{
    public function __construct(
        private ReservationDashboardService $dashboard,
        private TreatmentBookingsReportService $report,
        private TreatmentReservationAnalyticsService $analytics,
        private UpcomingJobUrgencyService $urgency,
    ) {}


    public function index(Request $request)
    {
        $view = in_array($request->query('view'), ['calendar', 'kanban', 'dashboard', 'reports'], true)
            ? $request->query('view')
            : 'dashboard';

        $defaults = TreatmentBookingsReportService::defaultDateRange();
        $reportFrom = $request->input('from', $defaults['from']);
        $reportTo = $request->input('to', $defaults['to']);
        $beauticianId = $request->integer('beautician_id') ?: null;
        $categoryId = $request->integer('treatment_category_id') ?: null;
        $spaBranchId = $request->integer('spa_branch_id') ?: null;
        $dateFilter = in_array($request->input('date_filter'), ['today', 'tomorrow', 'yesterday', 'all'], true)
            ? $request->input('date_filter')
            : 'today';
        $source = in_array($request->input('source'), ['manual', 'checkout'], true)
            ? $request->input('source')
            : null;
        $analyticsDays = TreatmentReservationAnalyticsService::DEFAULT_DAYS;
        $urgencyPayload = $this->urgency->forAdminTeam();

        return view('treatmentreservation::admin.reservations.index', [
            'activeView' => $view,
            'stats' => $this->dashboard->stats($beauticianId, $categoryId, $spaBranchId),
            'todayBookings' => $this->dashboard->todayCount($beauticianId, $categoryId, $spaBranchId),
            'dashboardData' => $view === 'dashboard'
                ? $this->dashboard->crmPayload($beauticianId, $categoryId, $spaBranchId, $dateFilter, $urgencyPayload)
                : null,
            'urgency' => $view === 'dashboard' ? $urgencyPayload : null,
            'analytics' => $view === 'dashboard'
                ? $this->analytics->overview($analyticsDays)
                : null,
            'analyticsCharts' => $view === 'dashboard'
                ? $this->analytics->chartPayload($analyticsDays)
                : null,
            'reportSummary' => $view === 'reports'
                ? $this->report->summary($reportFrom, $reportTo, $beauticianId, $categoryId, $source)
                : null,
            'beauticians' => Beautician::activeList(),
            'beauticianPickerOptions' => Beautician::activeListForCheckout(),
            'categories' => TreatmentCategory::active()->ordered()->get(),
            'spaBranches' => is_module_enabled('SpaBranch')
                ? \Modules\SpaBranch\Entities\SpaBranch::query()->where('is_active', true)->orderBy('position')->orderBy('name')->pluck('name', 'id')
                : collect(),
            'manualBookingProductCatalog' => app(ManualBookingProductCatalogService::class)->catalog(),
            'filters' => [
                'beautician_id' => $beauticianId,
                'treatment_category_id' => $categoryId,
                'spa_branch_id' => $spaBranchId,
                'date_filter' => $dateFilter,
                'month' => $request->input('month', now()->format('Y-m')),
                'from' => $reportFrom,
                'to' => $reportTo,
                'source' => $source,
            ],
        ]);
    }


    public function calendarEvents(Request $request): JsonResponse
    {
        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'beautician_id' => ['nullable', 'integer'],
            'treatment_category_id' => ['nullable', 'integer'],
        ]);

        $bookings = TreatmentBooking::query()
            ->forCalendar(
                $request->input('month'),
                $request->integer('beautician_id') ?: null,
                $request->integer('treatment_category_id') ?: null
            )
            ->get()
            ->map(fn (TreatmentBooking $booking) => $booking->appendAdminPayload($booking->toCalendarPayload()));

        return response()->json(['bookings' => $bookings]);
    }


    public function toggleSpecialistAvailability(Request $request, int $beautician): JsonResponse
    {
        $request->validate([
            'available' => ['required', 'boolean'],
            'date' => ['nullable', 'date'],
        ]);

        Beautician::query()->findOrFail($beautician);

        $date = $request->input('date', today()->toDateString());
        $available = $request->boolean('available');

        app(BeauticianAvailabilityService::class)->setCrmDayOff($beautician, $date, ! $available);

        return response()->json([
            'available' => $available,
            'beautician_id' => $beautician,
            'date' => $date,
        ]);
    }


    public function kanbanBoard(Request $request): JsonResponse
    {
        $bookings = TreatmentBooking::query()
            ->forKanban(
                $request->integer('beautician_id') ?: null,
                $request->integer('treatment_category_id') ?: null
            )
            ->get();

        $columns = [];

        foreach (TreatmentBooking::kanbanStatuses() as $status) {
            $columns[$status] = $bookings
                ->where('status', $status)
                ->values()
                ->map(fn (TreatmentBooking $booking) => $booking->appendAdminPayload($booking->toKanbanPayload()));
        }

        return response()->json(['columns' => $columns]);
    }


    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:' . implode(',', TreatmentBooking::kanbanStatuses())],
        ]);

        $booking = TreatmentBooking::findOrFail($id);
        $previousStatus = $booking->status;
        $booking->update(['status' => $request->input('status')]);

        app(TreatmentBookingActivityLogger::class)->logStatusChange(
            $booking,
            $previousStatus,
            $request->input('status')
        );

        app(BookingJobSheetOrderSync::class)->syncOrderStatus(
            $booking,
            $request->input('status')
        );

        $freshBooking = $booking->fresh();

        return response()->json([
            'booking' => $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload()),
        ]);
    }


    public function sendCustomerWhatsApp(Request $request, int $id, BookingCustomerWhatsAppService $whatsapp): JsonResponse
    {
        $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = TreatmentBooking::query()
            ->with(['beautician', 'product'])
            ->findOrFail($id);

        try {
            $whatsapp->send($booking, $request->input('message'));
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage() ?: trans('treatmentreservation::admin.calendar.whatsapp_failed'),
            ], 422);
        }

        app(TreatmentBookingActivityLogger::class)->logWhatsAppSent($booking);

        $freshBooking = $booking->fresh();

        return response()->json([
            'message' => trans('treatmentreservation::admin.calendar.whatsapp_sent'),
            'booking' => $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload()),
        ]);
    }


    public function customerProfile(Request $request, CustomerCrmProfileService $profiles): JsonResponse
    {
        $request->validate([
            'booking_id' => ['nullable', 'integer'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        try {
            if ($request->filled('booking_id')) {
                $booking = TreatmentBooking::query()->findOrFail($request->integer('booking_id'));

                return response()->json([
                    'profile' => $profiles->forBooking($booking),
                ]);
            }

            $phone = trim((string) $request->input('phone', ''));

            if ($phone === '') {
                return response()->json([
                    'message' => trans('treatmentreservation::admin.crm.profile_lookup_required'),
                ], 422);
            }

            return response()->json([
                'profile' => $profiles->forPhone($phone),
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }


    public function sendCustomerReminder(Request $request, int $id, CustomerAppointmentReminderService $reminders): JsonResponse
    {
        $request->validate([
            'resend' => ['nullable', 'boolean'],
        ]);

        $booking = TreatmentBooking::query()
            ->with(['beautician', 'product'])
            ->findOrFail($id);

        try {
            $reminders->sendManualReminder($booking, $request->boolean('resend'));
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage() ?: trans('treatmentreservation::admin.crm.reminder_failed'),
            ], 422);
        }

        $freshBooking = $booking->fresh();

        return response()->json([
            'message' => trans('treatmentreservation::admin.crm.reminder_sent'),
            'booking' => $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload()),
        ]);
    }


    public function export(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'beautician_id' => ['nullable', 'integer'],
            'treatment_category_id' => ['nullable', 'integer'],
            'source' => ['nullable', 'in:manual,checkout'],
        ]);

        $defaults = TreatmentBookingsReportService::defaultDateRange();
        $source = in_array($request->input('source'), ['manual', 'checkout'], true)
            ? $request->input('source')
            : null;

        return $this->report->exportCsv(
            $request->input('from', $defaults['from']),
            $request->input('to', $defaults['to']),
            $request->integer('beautician_id') ?: null,
            $request->integer('treatment_category_id') ?: null,
            $source
        );
    }


    public function exportPdf(Request $request): View
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'beautician_id' => ['nullable', 'integer'],
            'treatment_category_id' => ['nullable', 'integer'],
            'source' => ['nullable', 'in:manual,checkout'],
        ]);

        $defaults = TreatmentBookingsReportService::defaultDateRange();
        $from = $request->input('from', $defaults['from']);
        $to = $request->input('to', $defaults['to']);
        $beauticianId = $request->integer('beautician_id') ?: null;
        $categoryId = $request->integer('treatment_category_id') ?: null;
        $source = in_array($request->input('source'), ['manual', 'checkout'], true)
            ? $request->input('source')
            : null;

        return view('treatmentreservation::admin.reservations.print.report', [
            'from' => $from,
            'to' => $to,
            'summary' => $this->report->summary($from, $to, $beauticianId, $categoryId, $source),
            'bookings' => $this->report->bookings($from, $to, $beauticianId, $categoryId, $source),
            'breakdown' => $this->report->beauticianBreakdown($from, $to, $beauticianId, $categoryId, $source),
            'generatedAt' => now(),
        ]);
    }
}
