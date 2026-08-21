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
use Modules\TreatmentReservation\Services\BeauticianAppointmentReminderService;
use Modules\TreatmentReservation\Services\BookingCustomerWhatsAppService;
use Modules\TreatmentReservation\Services\ScheduleTbaBookingService;
use Modules\TreatmentReservation\Http\Requests\ScheduleTbaBookingRequest;
use Modules\TreatmentReservation\Services\BookingJobSheetOrderSync;
use Modules\TreatmentReservation\Services\ManualBookingProductCatalogService;
use Modules\TreatmentReservation\Services\TreatmentBookingsReportService;
use Modules\TreatmentReservation\Services\TreatmentReservationAnalyticsService;
use Modules\TreatmentReservation\Services\UpcomingJobUrgencyService;
use Modules\TreatmentReservation\Services\MalaysiaHolidayImportService;
use Modules\TreatmentReservation\Entities\TreatmentPublicHoliday;
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
        $rawDateFilter = $request->input('date_filter', 'today');
        $dateFilter = in_array($rawDateFilter, ['today', 'tomorrow', 'yesterday', 'all', 'custom'], true)
            ? $rawDateFilter
            : 'today';
        $customFilterDate = $request->input('filter_date');

        if ($dateFilter === 'custom') {
            if (! is_string($customFilterDate) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $customFilterDate)) {
                $dateFilter = 'today';
                $customFilterDate = null;
            }
        } else {
            $customFilterDate = null;
        }
        $source = in_array($request->input('source'), ['manual', 'checkout'], true)
            ? $request->input('source')
            : null;
        $analyticsDays = TreatmentReservationAnalyticsService::DEFAULT_DAYS;
        $urgencyPayload = $this->urgency->forAdminTeam();

        return view('treatmentreservation::admin.reservations.index', [
            'activeView' => $view,
            'stats' => $this->dashboard->stats($beauticianId, $categoryId, $spaBranchId),
            'todayBookings' => $this->dashboard->todayCount($beauticianId, $categoryId, $spaBranchId),
            'dashboardData' => in_array($view, ['dashboard', 'kanban'], true)
                ? $this->dashboard->crmPayload($beauticianId, $categoryId, $spaBranchId, $dateFilter, $urgencyPayload, $customFilterDate)
                : null,
            'urgency' => in_array($view, ['dashboard', 'kanban'], true) ? $urgencyPayload : null,
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
                'filter_date' => $customFilterDate,
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

    public function holidaysRange(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = $request->input('from');
        $to = $request->input('to');

        $holidays = TreatmentPublicHoliday::query()
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (TreatmentPublicHoliday $h) => $h->date->toDateString());

        $map = [];

        foreach ($holidays as $date => $items) {
            $first = $items->first();

            if (! $first) {
                continue;
            }

            $payloadItems = $items->map(function (TreatmentPublicHoliday $holiday) {
                return [
                    'label' => $holiday->name,
                    'color' => $holiday->color,
                    'kind' => $this->holidayKindForHolidayName((string) $holiday->name),
                    'states' => array_values($holiday->state_codes ?? []),
                    'day_name' => $holiday->day_name,
                    'is_subject_to_change' => (bool) $holiday->is_subject_to_change,
                ];
            })->values()->all();

            $map[$date] = [
                'label' => $first->name,
                'color' => $first->color,
                'kind' => $this->holidayKindForHolidayName((string) $first->name),
                'states' => array_values($first->state_codes ?? []),
                'day_name' => $first->day_name,
                'is_subject_to_change' => (bool) $first->is_subject_to_change,
                'items' => $payloadItems,
            ];
        }

        return response()->json(['holidays' => $map]);
    }

    private function holidayKindForHolidayName(string $name): string
    {
        $n = mb_strtolower($name);

        if (
            str_contains($n, 'merdeka')
            || str_contains($n, 'malaysia day')
            || str_contains($n, 'kebangsaan')
            || str_contains($n, 'national')
            || str_contains($n, 'hari malaysia')
            || str_contains($n, 'federal')
        ) {
            return 'national';
        }

        if (str_contains($n, 'labour') || str_contains($n, 'pekerja')) {
            return 'labour';
        }

        if (
            str_contains($n, 'raya')
            || str_contains($n, 'eid')
            || str_contains($n, 'muharram')
            || str_contains($n, 'ramadan')
            || str_contains($n, 'nabi')
            || str_contains($n, 'prophet')
            || str_contains($n, 'maul')
            || str_contains($n, 'awal muharram')
            || str_contains($n, 'arwah')
        ) {
            return 'religious';
        }

        if (
            str_contains($n, 'deepavali')
            || str_contains($n, 'thaipusam')
            || str_contains($n, 'christmas')
            || str_contains($n, 'krismas')
            || str_contains($n, 'weseak')
        ) {
            return 'festival';
        }

        return 'other';
    }

    public function importHolidays(Request $request): JsonResponse
    {
        $request->validate([
            'year' => ['required', 'integer', 'min:1900', 'max:3000'],
            'state' => ['nullable', 'string', 'max:10'],
        ]);

        $year = (int) $request->input('year');
        $state = $request->input('state');

        $imported = app(MalaysiaHolidayImportService::class)->importYear(
            $year,
            is_string($state) && $state !== '' ? $state : null
        );

        return response()->json([
            'ok' => true,
            'year' => $year,
            'state' => $state,
            'imported' => $imported,
        ]);
    }

    public function holidaysPage(Request $request): View
    {
        $year = (int) $request->integer('year') ?: now()->year;

        $rowCount = TreatmentPublicHoliday::query()
            ->whereYear('date', $year)
            ->count();

        $holidays = TreatmentPublicHoliday::query()
            ->whereYear('date', $year)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        return view('treatmentreservation::admin.reservations.holidays.index', [
            'year' => $year,
            'rowCount' => $rowCount,
            'holidays' => $holidays,
        ]);
    }

    public function importHolidaysFromForm(Request $request)
    {
        $request->validate([
            'year' => ['required', 'integer', 'min:1900', 'max:3000'],
            'state' => ['nullable', 'string', 'max:10'],
        ]);

        $year = (int) $request->input('year');
        $state = $request->input('state');

        $imported = app(MalaysiaHolidayImportService::class)->importYear(
            $year,
            is_string($state) && $state !== '' ? $state : null
        );

        return redirect()
            ->route('admin.treatment_reservations.holidays.index', ['year' => $year])
            ->withSuccess(trans('treatmentreservation::admin.holidays_import_success', [
                'count' => $imported,
                'year' => $year,
            ]));
    }

    public function updateHoliday(Request $request, TreatmentPublicHoliday $holiday): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'state_codes' => ['nullable', 'string', 'max:255'],
        ]);

        $stateCodesInput = trim((string) $request->input('state_codes', ''));

        $stateCodes = $stateCodesInput === ''
            ? null
            : array_values(array_filter(
                array_map(static fn (string $c) => strtoupper(trim($c)), explode(',', $stateCodesInput))
            ));

        $holiday->update([
            'name' => $request->input('name'),
            'color' => $request->input('color'),
            'state_codes' => $stateCodes,
        ]);

        return redirect()
            ->route('admin.treatment_reservations.holidays.index', ['year' => $holiday->date->format('Y')])
            ->withSuccess(trans('treatmentreservation::admin.holidays_update_success', [
                'date' => $holiday->date->toDateString(),
            ]));
    }

    public function deleteHoliday(Request $request, TreatmentPublicHoliday $holiday): \Illuminate\Http\RedirectResponse
    {
        $year = $holiday->date->format('Y');

        $holiday->delete();

        return redirect()
            ->route('admin.treatment_reservations.holidays.index', ['year' => $year])
            ->withSuccess(trans('treatmentreservation::admin.holidays_delete_success', [
                'date' => $holiday->date->toDateString(),
            ]));
    }

    public function storeHoliday(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'state_codes' => ['nullable', 'string', 'max:255'],
        ]);

        $date = (string) $request->input('date');
        $name = (string) $request->input('name');
        $color = (string) $request->input('color');
        $stateCodesInput = trim((string) $request->input('state_codes', ''));

        $stateCodes = $stateCodesInput === ''
            ? null
            : array_values(array_filter(
                array_map(static fn (string $c) => strtoupper(trim($c)), explode(',', $stateCodesInput))
            ));

        // Treat manual add as overriding the same API master-data slot for this date.
        $source = 'malaysia-holiday-api:v1';

        TreatmentPublicHoliday::query()
            ->where('date', $date)
            ->where('source', $source)
            ->delete();

        TreatmentPublicHoliday::query()->create([
            'date' => $date,
            'name' => $name,
            'day_name' => null,
            'state_codes' => $stateCodes,
            'is_subject_to_change' => false,
            'color' => $color,
            'source' => $source,
        ]);

        return redirect()
            ->route('admin.treatment_reservations.holidays.index', ['year' => substr($date, 0, 4)])
            ->withSuccess(trans('treatmentreservation::admin.holidays_add_success', [
                'date' => $date,
            ]));
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




    public function listTba(Request $request): JsonResponse
    {
        $bookings = TreatmentBooking::query()
            ->withActiveOrder()
            ->withTreatmentProduct()
            ->with(['beautician.files', 'product', 'category', 'order'])
            ->tbaSchedule()
            ->when($request->integer('beautician_id') ?: null, fn ($q, $id) => $q->where('beautician_id', $id))
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn (TreatmentBooking $booking) => $booking->appendAdminPayload($booking->toKanbanPayload()));

        return response()->json(['bookings' => $bookings]);
    }


    public function scheduleTba(ScheduleTbaBookingRequest $request, int $id, ScheduleTbaBookingService $scheduler): JsonResponse
    {
        $booking = TreatmentBooking::query()->findOrFail($id);

        try {
            $updated = $scheduler->schedule(
                $booking,
                $request->validated(),
                $request->user(),
                $request->boolean('notify_customer', true),
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => trans('treatmentreservation::admin.tba.scheduled'),
            'booking' => $updated->appendAdminPayload($updated->toKanbanPayload()),
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


    public function sendBeauticianReminder(Request $request, int $id, BeauticianAppointmentReminderService $reminders): JsonResponse
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
                'message' => $exception->getMessage() ?: trans('treatmentreservation::admin.crm.beautician_reminder_failed'),
            ], 422);
        }

        $freshBooking = $booking->fresh();

        return response()->json([
            'message' => trans('treatmentreservation::admin.crm.beautician_reminder_sent'),
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
