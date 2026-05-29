<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Entities\TreatmentCategory;
use Modules\TreatmentReservation\Services\ReservationDashboardService;
use Modules\TreatmentReservation\Services\TreatmentBookingActivityLogger;
use Modules\TreatmentReservation\Services\BookingCustomerWhatsAppService;
use Modules\TreatmentReservation\Services\BookingJobSheetOrderSync;
use Modules\TreatmentReservation\Services\TreatmentBookingsReportService;
use Modules\TreatmentReservation\Services\TreatmentReservationAnalyticsService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationController extends Controller
{
    public function __construct(
        private ReservationDashboardService $dashboard,
        private TreatmentBookingsReportService $report,
        private TreatmentReservationAnalyticsService $analytics
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
        $analyticsDays = TreatmentReservationAnalyticsService::DEFAULT_DAYS;

        return view('treatmentreservation::admin.reservations.index', [
            'activeView' => $view,
            'stats' => $this->dashboard->stats(),
            'todayBookings' => TreatmentBooking::query()
                ->withTreatmentProduct()
                ->whereDate('appointment_date', today())
                ->whereNot('status', TreatmentBooking::STATUS_CANCELED)
                ->count(),
            'analytics' => $view === 'dashboard'
                ? $this->analytics->overview($analyticsDays)
                : null,
            'analyticsCharts' => $view === 'dashboard'
                ? $this->analytics->chartPayload($analyticsDays)
                : null,
            'reportSummary' => $view === 'reports'
                ? $this->report->summary($reportFrom, $reportTo, $beauticianId, $categoryId)
                : null,
            'beauticians' => Beautician::activeList(),
            'categories' => TreatmentCategory::active()->ordered()->get(),
            'filters' => [
                'beautician_id' => $beauticianId,
                'treatment_category_id' => $categoryId,
                'month' => $request->input('month', now()->format('Y-m')),
                'from' => $reportFrom,
                'to' => $reportTo,
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


    public function export(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'beautician_id' => ['nullable', 'integer'],
            'treatment_category_id' => ['nullable', 'integer'],
        ]);

        $defaults = TreatmentBookingsReportService::defaultDateRange();

        return $this->report->exportCsv(
            $request->input('from', $defaults['from']),
            $request->input('to', $defaults['to']),
            $request->integer('beautician_id') ?: null,
            $request->integer('treatment_category_id') ?: null
        );
    }


    public function exportPdf(Request $request): View
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'beautician_id' => ['nullable', 'integer'],
            'treatment_category_id' => ['nullable', 'integer'],
        ]);

        $defaults = TreatmentBookingsReportService::defaultDateRange();
        $from = $request->input('from', $defaults['from']);
        $to = $request->input('to', $defaults['to']);
        $beauticianId = $request->integer('beautician_id') ?: null;
        $categoryId = $request->integer('treatment_category_id') ?: null;

        return view('treatmentreservation::admin.reservations.print.report', [
            'from' => $from,
            'to' => $to,
            'summary' => $this->report->summary($from, $to, $beauticianId, $categoryId),
            'bookings' => $this->report->bookings($from, $to, $beauticianId, $categoryId),
            'breakdown' => $this->report->beauticianBreakdown($from, $to, $beauticianId, $categoryId),
            'generatedAt' => now(),
        ]);
    }
}
