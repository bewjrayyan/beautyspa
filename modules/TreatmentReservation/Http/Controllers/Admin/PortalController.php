<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Beautician\Entities\Beautician;
use Modules\Product\Entities\Product;
use Modules\Order\Events\OrderUpdated;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Entities\TreatmentCategory;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;
use Modules\TreatmentReservation\Services\BookingCustomerWhatsAppService;
use Modules\TreatmentReservation\Services\BookingSelfService;
use Modules\TreatmentReservation\Services\ScheduleTbaBookingService;
use Modules\TreatmentReservation\Http\Requests\ScheduleTbaBookingRequest;
use Modules\TreatmentReservation\Http\Requests\RescheduleTreatmentBookingRequest;
use Modules\TreatmentReservation\Services\RescheduleTreatmentBookingService;
use Modules\TreatmentReservation\Services\BookingJobSheetOrderSync;
use Modules\TreatmentReservation\Services\CustomerAppointmentReminderService;
use Modules\TreatmentReservation\Services\CustomerCrmProfileService;
use Modules\TreatmentReservation\Services\ReservationDashboardService;
use Modules\TreatmentReservation\Services\TreatmentBookingActivityLogger;
use Modules\TreatmentReservation\Services\UpcomingJobUrgencyService;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityService;
use Modules\User\Services\OneSenderWhatsAppService;

class PortalController extends Controller
{
    public function __construct(
        private ReservationDashboardService $dashboard,
        private UpcomingJobUrgencyService $urgency,
    ) {}


    public function dashboard(Request $request): View
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');
        $beautician->loadMissing(['files', 'user', 'spaBranches']);

        $lockPortalFilters = ! $this->isAdminBeauticianPreview($request);
        $filters = $this->resolvePortalCrmFilters($request, $beautician, $lockPortalFilters);
        $beauticianId = $beautician->id;
        $categoryId = $filters['treatment_category_id'];
        $spaBranchId = $filters['spa_branch_id'];
        $dateFilter = $filters['date_filter'];
        $customFilterDate = $filters['filter_date'];
        $urgencyPayload = $this->urgency->forBeautician($beauticianId);

        $crmRoutes = $this->crmApiRoutes($request, $beautician);

        return view('treatmentreservation::admin.portal.dashboard', array_merge([
            'beautician' => $beautician,
            'activeView' => 'dashboard',
            'stats' => $this->dashboard->stats($beauticianId, $categoryId, $spaBranchId),
            'heroStats' => $this->dashboard->stats($beauticianId),
            'todayAppointments' => $this->dashboard->todayActiveAppointments($beauticianId),
            'dashboardData' => $this->dashboard->crmPayload(
                $beauticianId,
                $categoryId,
                $spaBranchId,
                $dateFilter,
                $urgencyPayload,
                $customFilterDate,
                $filters['filter_date_to'] ?? null,
            ),
            'urgency' => $urgencyPayload,
            // CRM workspace uses Needs attention — analytics charts live on reports surfaces.
            'analytics' => null,
            'analyticsCharts' => null,
            'categories' => TreatmentCategory::active()->ordered()->get(),
            'spaBranches' => $this->spaBranchesForBeautician($beautician, $lockPortalFilters),
            'beauticianPickerOptions' => collect(Beautician::activeListForCheckout())
                ->where('id', $beauticianId)
                ->values()
                ->all(),
            'filters' => array_merge($filters, [
                'beautician_id' => $beauticianId,
                'month' => $request->input('month', now()->format('Y-m')),
            ]),
            'portalFilterContext' => $this->portalFilterContext($beautician, $filters, $lockPortalFilters),
            'crmRoutes' => $crmRoutes,
            'crmCanEdit' => true,
            'crmSpecialistProfileUrl' => $this->isAdminBeauticianPreview($request)
                ? route('admin.beauticians.portal.availability', $beautician->id)
                : route('admin.treatment_reservations.portal.availability'),
            'portalDashboard' => true,
            'crmSelfScoped' => true,
        ], $this->portalPreviewContext($request, $beautician)));
    }


    public function jobSheet(Request $request)
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');
        $beautician->loadMissing(['files', 'spaBranches']);

        $viewerBeauticianId = (int) $beautician->id;
        $todayAppointments = $this->dashboard->todayActiveAppointments($viewerBeauticianId);

        $activeView = in_array($request->query('view'), ['kanban', 'calendar'], true)
            ? $request->query('view')
            : 'kanban';

        $calendarFocus = $request->boolean('focus') && $activeView === 'calendar';
        $calendarFocusBookingId = $request->integer('booking_id') ?: null;

        if ($calendarFocusBookingId && ! TreatmentBooking::query()
            ->whereKey($calendarFocusBookingId)
            ->where('beautician_id', $beautician->id)
            ->exists()) {
            $calendarFocusBookingId = null;
        }

        $portalContext = $this->portalContext($request, $beautician);

        return view('treatmentreservation::admin.portal.job_sheet', array_merge([
            'beautician' => $beautician,
            'stats' => $this->dashboard->stats($viewerBeauticianId),
            'performanceStats' => $this->dashboard->statsForBeautician($beautician->id),
            'todayAppointments' => $todayAppointments,
            'todayBookingsPayload' => $todayAppointments
                ->map(fn (TreatmentBooking $booking) => $booking->toPortalKanbanPayload($viewerBeauticianId))
                ->values(),
            'activeView' => $activeView,
            'calendarFocus' => $calendarFocus,
            'calendarFocusBookingId' => $calendarFocusBookingId,
            'beauticianPickerOptions' => Beautician::activeListForCheckout(),
        ], $portalContext));
    }


    public function calendarPage(Request $request)
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');
        $beautician->loadMissing(['files', 'spaBranches']);

        $viewerBeauticianId = (int) $beautician->id;
        $todayAppointments = $this->dashboard->todayActiveAppointments($viewerBeauticianId);
        $portalContext = $this->portalContext($request, $beautician);

        $calendarFocusBookingId = $request->integer('booking_id') ?: null;
        $focusBooking = null;

        if ($calendarFocusBookingId) {
            $focusBooking = TreatmentBooking::query()
                ->whereKey($calendarFocusBookingId)
                ->where('beautician_id', $beautician->id)
                ->first();

            if (! $focusBooking) {
                $calendarFocusBookingId = null;
            }
        }

        $initialMonth = $request->input('month')
            ?: ($focusBooking?->appointment_date?->format('Y-m'))
            ?: now()->format('Y-m');

        return view('treatmentreservation::admin.portal.calendar', array_merge([
            'beautician' => $beautician,
            'stats' => $this->dashboard->stats($viewerBeauticianId),
            'performanceStats' => $this->dashboard->statsForBeautician($beautician->id),
            'todayAppointments' => $todayAppointments,
            'todayBookingsPayload' => $todayAppointments
                ->map(fn (TreatmentBooking $booking) => $booking->toPortalKanbanPayload($viewerBeauticianId))
                ->values(),
            'activeView' => 'calendar',
            'calendarFocus' => $request->boolean('focus') || filled($calendarFocusBookingId),
            'calendarFocusBookingId' => $calendarFocusBookingId,
            'calendarInitialMonth' => $initialMonth,
            'beauticianPickerOptions' => Beautician::activeListForCheckout(),
        ], $portalContext));
    }


    /**
     * @return array{adminPortalPreview: bool, portalApiRoutes: array<string, string>, backUrl: string|null}
     */
    private function portalContext(Request $request, Beautician $beautician): array
    {
        if (! $request->routeIs('admin.beauticians.portal*')) {
            return [
                'adminPortalPreview' => false,
                'portalApiRoutes' => [
                    'calendar' => route('admin.treatment_reservations.portal.calendar'),
                    'calendar_details' => route('admin.treatment_reservations.portal.calendar.event', ['booking' => '__ID__']),
                    'kanban' => route('admin.treatment_reservations.portal.kanban'),
                    'update_status' => route('admin.treatment_reservations.portal.update_status', ['id' => '__ID__']),
                    'update_notes' => route('admin.treatment_reservations.portal.update_notes', ['id' => '__ID__']),
                    'send_whatsapp' => route('admin.treatment_reservations.portal.send_whatsapp', ['id' => '__ID__']),
                    'consultation' => route('admin.treatment_reservations.portal.consultation', ['id' => '__ID__']),
                    'reschedule' => route('admin.treatment_reservations.portal.reschedule', ['id' => '__ID__']),
                    'slots' => route('admin.treatment_reservations.portal.reschedule_slots', ['id' => '__ID__']),
                    'dates' => route('admin.treatment_reservations.portal.reschedule_dates', ['id' => '__ID__']),
                    'tba_slots' => route('admin.treatment_reservations.portal.manual_bookings.slots'),
                    'customer_profile' => route('admin.treatment_reservations.portal.customer_profile'),
                    'reminder' => route('admin.treatment_reservations.portal.send_reminder', ['id' => '__ID__']),
                ],
                'backUrl' => null,
            ];
        }

        $routeParams = ['id' => $beautician->id];

        return [
            'adminPortalPreview' => $this->isAdminBeauticianPreview($request, $beautician),
            'crmRoutes' => $this->crmApiRoutes($request, $beautician),
            'portalApiRoutes' => [
                'calendar' => route('admin.beauticians.portal.calendar', $routeParams),
                'calendar_details' => route('admin.beauticians.portal.calendar.event', ['id' => $beautician->id, 'booking' => '__ID__']),
                'kanban' => route('admin.beauticians.portal.kanban', $routeParams),
                'update_status' => route('admin.beauticians.portal.update_status', ['id' => $beautician->id, 'booking' => '__ID__']),
                'update_notes' => route('admin.beauticians.portal.update_notes', ['id' => $beautician->id, 'booking' => '__ID__']),
                'send_whatsapp' => route('admin.beauticians.portal.send_whatsapp', ['id' => $beautician->id, 'booking' => '__ID__']),
                'consultation' => route('admin.beauticians.portal.consultation', ['id' => $beautician->id, 'booking' => '__ID__']),
                'reschedule' => route('admin.beauticians.portal.reschedule', ['id' => $beautician->id, 'booking' => '__ID__']),
                'slots' => route('admin.beauticians.portal.reschedule_slots', [
                    'id' => $beautician->id,
                    'booking' => '__ID__',
                ]),
                'dates' => route('admin.beauticians.portal.reschedule_dates', [
                    'id' => $beautician->id,
                    'booking' => '__ID__',
                ]),
                'tba_slots' => $this->isAdminBeauticianPreview($request, $beautician)
                    ? route('admin.treatment_reservations.manual_bookings.slots')
                    : route('admin.treatment_reservations.portal.manual_bookings.slots'),
                'customer_profile' => route('admin.beauticians.portal.customer_profile', $routeParams),
                'reminder' => route('admin.beauticians.portal.send_reminder', ['id' => $beautician->id, 'booking' => '__ID__']),
            ],
            'backUrl' => $this->isAdminBeauticianPreview($request, $beautician)
                ? route('admin.beauticians.edit', $beautician)
                : null,
        ];
    }


    public function calendarEvents(Request $request): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $viewerBeauticianId = (int) $beautician->id;
        $bookings = TreatmentBooking::query()
            ->forCalendar($request->input('month'), $viewerBeauticianId)
            ->get()
            ->map(fn (TreatmentBooking $booking) => $booking->toCalendarSummaryPayload($viewerBeauticianId));

        return response()->json(['bookings' => $bookings]);
    }


    public function calendarEvent(Request $request, int $id): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        // beauticians/{id}/portal/calendar/events/{booking} passes [Request, id, booking]
        // by position — never bind the beautician {id} as the booking id.
        $bookingId = $this->bookingIdFromRoute($request, $id);

        $booking = TreatmentBooking::query()
            ->visibleOnCalendar()
            ->withTreatmentProduct()
            ->withCalendarDetails()
            ->where('beautician_id', $beautician->id)
            ->findOrFail($bookingId);

        if ($this->isAdminBeauticianPreview($request, $beautician)) {
            $payload = $booking->appendAdminPayload($booking->toCalendarPayload());
        } else {
            $payload = $booking->toPortalCalendarPayload((int) $beautician->id);
        }

        $payload['details_loaded'] = true;

        return response()->json(['booking' => $payload]);
    }


    public function kanbanBoard(Request $request): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');
        $beautician->loadMissing(['files', 'spaBranches']);

        $viewerBeauticianId = (int) $beautician->id;
        $bookings = TreatmentBooking::query()
            ->forKanban($viewerBeauticianId, $request->integer('treatment_category_id') ?: null)
            ->get();

        $columns = [];

        foreach (TreatmentBooking::kanbanStatuses() as $status) {
            $columns[$status] = $bookings
                ->where('status', $status)
                ->values()
                ->map(fn (TreatmentBooking $booking) => $booking->toPortalKanbanPayload($viewerBeauticianId));
        }

        return response()->json(['columns' => $columns]);
    }


    public function updateStatus(Request $request, int $id): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'status' => ['required', 'in:' . implode(',', TreatmentBooking::kanbanStatuses())],
        ]);

        $booking = TreatmentBooking::query()
            ->where('beautician_id', $beautician->id)
            ->findOrFail($this->bookingIdFromRoute($request, $id));

        $nextStatus = $request->input('status');

        if (
            $nextStatus === TreatmentBooking::STATUS_IN_PROGRESS
            && $booking->requiresScheduleBeforeStart()
        ) {
            return response()->json([
                'message' => trans('treatmentreservation::admin.crm.error_schedule_before_start'),
                'code' => 'schedule_required',
                'can_schedule_tba' => $booking->canScheduleTba(),
            ], 422);
        }


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

        if ($freshBooking->order_id) {
            $order = $freshBooking->order()->first();

            if ($order) {
                event(new OrderUpdated($order));
            }
        }

        return response()->json([
            'booking' => $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload()),
        ]);
    }


    public function updateBeauticianNotes(Request $request, int $id): JsonResponse
    {
        /** @var Beautician|null $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $time = $this->normalizeWorkLogTime($request->input('beautician_notes_time'));
        if ($time !== null) {
            $request->merge(['beautician_notes_time' => $time]);
        }

        $request->validate([
            'beautician_notes' => ['nullable', 'string', 'max:5000'],
            'beautician_notes_date' => ['nullable', 'required_with:beautician_notes_time', 'date_format:Y-m-d'],
            'beautician_notes_time' => ['nullable', 'required_with:beautician_notes_date', 'date_format:H:i'],
            'beautician_checklist' => ['sometimes', 'array', 'max:20'],
            'beautician_checklist.*.id' => ['nullable', 'string', 'max:64'],
            'beautician_checklist.*.label' => ['required', 'string', 'max:160'],
            'beautician_checklist.*.completed' => ['required', 'boolean'],
            'beautician_checklist.*.completed_at' => ['nullable', 'date'],
        ]);

        $bookingQuery = TreatmentBooking::query();

        if ($beautician) {
            $bookingQuery->where('beautician_id', $beautician->id);
        }

        $booking = $bookingQuery->findOrFail($this->bookingIdFromRoute($request, $id));

        $previousNotes = $booking->beautician_notes;
        $previousNotesAt = $booking->beautician_notes_at?->format('Y-m-d H:i:s');
        $previousChecklist = collect($booking->beautician_checklist ?? [])->values()->all();
        $existingChecklist = collect($previousChecklist)->keyBy('id');
        if ($request->has('beautician_checklist')) {
            foreach (array_values($request->input('beautician_checklist', [])) as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                if (trim((string) ($item['label'] ?? '')) === '') {
                    throw ValidationException::withMessages([
                        "beautician_checklist.{$index}.label" => [
                            trans('treatmentreservation::admin.calendar.work_log_empty_checklist_item'),
                        ],
                    ]);
                }
            }
        }

        $checklist = $request->has('beautician_checklist')
            ? collect($request->input('beautician_checklist', []))
                ->filter(fn ($item) => is_array($item))
                ->map(function (array $item) use ($existingChecklist) {
                    $id = filled($item['id'] ?? null)
                        ? (string) $item['id']
                        : (string) Str::uuid();
                    $completed = filter_var($item['completed'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $existing = $existingChecklist->get($id);

                    $completedAt = null;
                    if ($completed) {
                        if (filled($item['completed_at'] ?? null)) {
                            try {
                                $completedAt = \Carbon\Carbon::parse($item['completed_at'])->toIso8601String();
                            } catch (\Throwable $e) {
                                $completedAt = null;
                            }
                        }

                        $completedAt = $completedAt
                            ?? (is_array($existing) ? ($existing['completed_at'] ?? null) : null)
                            ?? now()->toIso8601String();
                    }

                    return [
                        'id' => $id,
                        'label' => trim((string) ($item['label'] ?? '')),
                        'completed' => $completed,
                        'completed_at' => $completedAt,
                    ];
                })
                ->filter(fn (array $item) => $item['label'] !== '')
                ->values()
                ->all()
            : $previousChecklist;

        $notesAt = null;
        if (filled($request->input('beautician_notes_date')) && filled($request->input('beautician_notes_time'))) {
            try {
                $notesAt = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $request->input('beautician_notes_date') . ' ' . $request->input('beautician_notes_time')
                );
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'beautician_notes_time' => [trans('validation.date_format', [
                        'attribute' => 'beautician notes time',
                        'format' => 'H:i',
                    ])],
                ]);
            }
        } elseif ($request->has('beautician_checklist')) {
            $notesAt = collect($checklist)
                ->filter(fn (array $item) => ($item['completed'] ?? false) && filled($item['completed_at'] ?? null))
                ->map(function (array $item) {
                    try {
                        return Carbon::parse($item['completed_at']);
                    } catch (\Throwable $e) {
                        return null;
                    }
                })
                ->filter()
                ->sortByDesc(fn (Carbon $date) => $date->timestamp)
                ->first() ?? $booking->beautician_notes_at;
        }

        $workLogChanged = $previousNotesAt !== $notesAt?->format('Y-m-d H:i:s')
            || $previousChecklist !== $checklist;

        $attributes = [
            'beautician_notes' => $request->input('beautician_notes'),
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn($booking->getTable(), 'beautician_notes_at')) {
            $attributes['beautician_notes_at'] = $notesAt;
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn($booking->getTable(), 'beautician_checklist')) {
            $attributes['beautician_checklist'] = $checklist;
        }

        $booking->update($attributes);

        try {
            app(TreatmentBookingActivityLogger::class)->logBeauticianNotes(
                $booking,
                $previousNotes,
                $request->input('beautician_notes')
            );

            if ($workLogChanged) {
                app(TreatmentBookingActivityLogger::class)->logTreatmentWorkLog(
                    $booking,
                    collect($checklist)->where('completed', true)->count(),
                    count($checklist)
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $freshBooking = $booking->fresh() ?? $booking;

        if ($freshBooking->order_id) {
            try {
                $order = $freshBooking->order()->first();

                if ($order) {
                    event(new OrderUpdated($order));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            $payload = $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload());
        } catch (\Throwable $e) {
            report($e);
            $payload = $freshBooking->toKanbanPayload();
        }

        return response()->json([
            'booking' => $payload,
        ]);
    }


    private function normalizeWorkLogTime(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{1,2}:\d{2}$/', $value) === 1) {
            return strlen($value) === 4 ? '0'.$value : $value;
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return $value;
        }
    }




    public function listTba(Request $request): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');
        $beautician->loadMissing(['files', 'spaBranches']);

        $viewerBeauticianId = (int) $beautician->id;
        $bookings = TreatmentBooking::query()
            ->withActiveOrder()
            ->withTreatmentProduct()
            ->with(['beautician.files', 'product.files', 'category', 'order'])
            ->where('beautician_id', $viewerBeauticianId)
            ->tbaSchedule()
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn (TreatmentBooking $booking) => TreatmentBooking::applyPortalViewerScope(
                $booking->appendAdminPayload($booking->toKanbanPayload()),
                $viewerBeauticianId
            ));

        return response()->json(['bookings' => $bookings]);
    }


    public function scheduleTba(ScheduleTbaBookingRequest $request, int $id, ScheduleTbaBookingService $scheduler): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');
        $booking = TreatmentBooking::query()
            ->where('beautician_id', $beautician->id)
            ->findOrFail($id);

        $payload = $request->validated();
        $payload['beautician_id'] = $beautician->id;

        try {
            $updated = $scheduler->schedule(
                $booking,
                $payload,
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


    public function reschedule(
        RescheduleTreatmentBookingRequest $request,
        int $id,
        RescheduleTreatmentBookingService $scheduler,
    ): JsonResponse {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');
        $booking = TreatmentBooking::query()
            ->where('beautician_id', $beautician->id)
            ->findOrFail($this->bookingIdFromRoute($request, $id));

        try {
            $result = $scheduler->reschedule(
                $booking,
                $request->validated(),
                $request->user(),
                $request->boolean('notify_customer', true),
                $request->boolean('notify_beautician', true),
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => trans('treatmentreservation::admin.reschedule.saved'),
            'booking' => TreatmentBooking::applyPortalViewerScope(
                $result['booking']->appendAdminPayload($result['booking']->toKanbanPayload()),
                (int) $beautician->id,
            ),
            'notifications' => [
                'customer' => $result['customer_notified'],
                'beautician' => $result['beautician_notified'],
            ],
        ]);
    }


    public function rescheduleSlots(Request $request, int $id, AppointmentAvailabilityService $availability): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);
        $booking = TreatmentBooking::query()
            ->with('order')
            ->where('beautician_id', $beautician->id)
            ->findOrFail($this->bookingIdFromRoute($request, $id));

        if (! $booking->canRescheduleAppointment()) {
            return response()->json([
                'message' => trans('treatmentreservation::admin.reschedule.not_allowed'),
            ], 422);
        }

        $productId = (int) ($booking->product_id ?? 0);
        $branchId = (int) ($booking->spa_branch_id ?? $booking->order?->spa_branch_id ?? 0);

        if ($productId && $branchId) {
            $slots = $availability->availableSlots(
                $productId,
                $branchId,
                $data['date'],
                (int) $beautician->id,
                (int) $booking->id,
            );
        } else {
            $slots = app(BeauticianAvailabilityService::class)->availableSlots(
                (int) $beautician->id,
                $data['date'],
                (int) $booking->id,
            );
        }

        return response()->json(['slots' => $slots])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function rescheduleDates(Request $request, int $id, BookingSelfService $availability): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');
        $data = $request->validate([
            'from' => ['required', 'date', 'after_or_equal:today'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        if (Carbon::parse($data['from'])->diffInDays(Carbon::parse($data['to'])) > 42) {
            throw ValidationException::withMessages(['to' => trans('validation.max.numeric', [
                'attribute' => 'to',
                'max' => 42,
            ])]);
        }

        $booking = TreatmentBooking::query()
            ->with('order')
            ->where('beautician_id', $beautician->id)
            ->findOrFail($this->bookingIdFromRoute($request, $id));

        if (! $booking->canRescheduleAppointment()) {
            return response()->json([
                'message' => trans('treatmentreservation::admin.reschedule.not_allowed'),
            ], 422);
        }

        return response()->json([
            'dates' => $availability->availableDatesForBooking($booking, $data['from'], $data['to']),
            'from' => $data['from'],
            'to' => $data['to'],
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function sendCustomerWhatsApp(Request $request, int $id, BookingCustomerWhatsAppService $whatsapp): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = TreatmentBooking::query()
            ->with(['beautician', 'product'])
            ->where('beautician_id', $beautician->id)
            ->findOrFail($this->bookingIdFromRoute($request, $id));

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
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'booking_id' => ['required', 'integer'],
        ]);

        try {
            $booking = TreatmentBooking::query()
                ->where('beautician_id', $beautician->id)
                ->findOrFail($request->integer('booking_id'));

            return response()->json([
                'profile' => $profiles->forBooking($booking, (int) $beautician->id),
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }


    public function sendCustomerReminder(Request $request, int $id, CustomerAppointmentReminderService $reminders): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'resend' => ['nullable', 'boolean'],
        ]);

        $booking = TreatmentBooking::query()
            ->with(['beautician', 'product'])
            ->where('beautician_id', $beautician->id)
            ->findOrFail($this->bookingIdFromRoute($request, $id));

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


    public function toggleOwnAvailability(Request $request): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'available' => ['required', 'boolean'],
            'date' => ['nullable', 'date'],
        ]);

        $date = $request->input('date', today()->toDateString());
        $available = $request->boolean('available');

        app(BeauticianAvailabilityService::class)->setCrmDayOff($beautician->id, $date, ! $available);

        return response()->json([
            'available' => $available,
            'beautician_id' => $beautician->id,
            'date' => $date,
        ]);
    }


    /**
     * @return array{
     *     date_filter: string,
     *     filter_date: string|null,
     *     treatment_category_id: int|null,
     *     spa_branch_id: int|null
     * }
     */
    private function crmFiltersFromRequest(Request $request): array
    {
        $crmDateFilters = $this->dashboard->normalizeCrmDateFilters(
            (string) $request->input('date_filter', 'today'),
            $request->input('filter_date'),
            $request->input('filter_date_to'),
        );

        return [
            'date_filter' => $crmDateFilters['date_filter'],
            'filter_date' => $crmDateFilters['filter_date'],
            'filter_date_to' => $crmDateFilters['filter_date_to'],
            'treatment_category_id' => $request->integer('treatment_category_id') ?: null,
            'spa_branch_id' => $request->integer('spa_branch_id') ?: null,
        ];
    }


    /**
     * @return array{
     *     date_filter: string,
     *     filter_date: string|null,
     *     treatment_category_id: int|null,
     *     spa_branch_id: int|null
     * }
     */
    private function resolvePortalCrmFilters(Request $request, Beautician $beautician, bool $lockToProfile): array
    {
        $filters = $this->crmFiltersFromRequest($request);

        if (! $lockToProfile || ! is_module_enabled('SpaBranch')) {
            return $filters;
        }

        $assignedBranches = $beautician->spaBranches
            ->where('is_active', true)
            ->sortBy('position')
            ->values();

        if ($assignedBranches->isEmpty()) {
            $filters['spa_branch_id'] = null;

            return $filters;
        }

        $assignedIds = $assignedBranches->pluck('id')->map(fn ($id) => (int) $id)->all();
        $requestedBranchId = $filters['spa_branch_id'];

        if ($assignedBranches->count() === 1) {
            $filters['spa_branch_id'] = $assignedIds[0];

            return $filters;
        }

        if ($requestedBranchId && in_array($requestedBranchId, $assignedIds, true)) {
            $filters['spa_branch_id'] = $requestedBranchId;
        } else {
            $filters['spa_branch_id'] = null;
        }

        return $filters;
    }


    /**
     * @return array{
     *     locked: bool,
     *     beautician_name: string,
     *     beautician_color: string,
     *     beautician_initial: string,
     *     beautician_avatar: string|null,
     *     branch_locked: bool,
     *     branch_name: string|null,
     *     branch_picker: bool
     * }
     */
    private function portalFilterContext(Beautician $beautician, array $filters, bool $locked): array
    {
        $assignedBranches = is_module_enabled('SpaBranch')
            ? $beautician->spaBranches->where('is_active', true)->sortBy('position')->values()
            : collect();

        $activeBranchId = $filters['spa_branch_id'] ?? null;
        $activeBranchName = $activeBranchId
            ? ($assignedBranches->firstWhere('id', $activeBranchId)?->name)
            : null;

        return [
            'locked' => $locked,
            'beautician_name' => $beautician->name,
            'beautician_color' => $beautician->profile_color ?: '#6366f1',
            'beautician_initial' => $beautician->initials,
            'beautician_avatar' => $beautician->displayAvatarUrl(),
            'branch_locked' => $locked && $assignedBranches->count() === 1,
            'branch_name' => $activeBranchName ?: ($assignedBranches->count() === 1 ? $assignedBranches->first()?->name : null),
            'branch_picker' => $locked && $assignedBranches->count() > 1,
        ];
    }


    /**
     * @return array<string, string>
     */
    private function crmApiRoutes(Request $request, Beautician $beautician): array
    {
        if ($request->routeIs('admin.beauticians.portal*')) {
            $routeParams = ['id' => $beautician->id];
            $isPreview = $this->isAdminBeauticianPreview($request, $beautician);

            return [
                'formAction' => route('admin.beauticians.portal.dashboard', $routeParams),
                'calendar' => route('admin.beauticians.portal.calendar', $routeParams),
                'bookingDetails' => route('admin.beauticians.portal.calendar.event', ['id' => $beautician->id, 'booking' => '__ID__']),
                'calendarFullView' => route('admin.beauticians.portal.calendar_page', ['id' => $beautician->id, 'focus' => 1]),
                'updateStatus' => route('admin.beauticians.portal.update_status', ['id' => $beautician->id, 'booking' => '__ID__']),
                'updateNotes' => route('admin.beauticians.portal.update_notes', ['id' => $beautician->id, 'booking' => '__ID__']),
                'reschedule' => route('admin.beauticians.portal.reschedule', ['id' => $beautician->id, 'booking' => '__ID__']),
                'rescheduleSlots' => route('admin.beauticians.portal.reschedule_slots', ['id' => $beautician->id, 'booking' => '__ID__']),
                'rescheduleDates' => route('admin.beauticians.portal.reschedule_dates', ['id' => $beautician->id, 'booking' => '__ID__']),
                'whatsapp' => route('admin.beauticians.portal.send_whatsapp', ['id' => $beautician->id, 'booking' => '__ID__']),
                'consultation' => route('admin.beauticians.portal.consultation', ['id' => $beautician->id, 'booking' => '__ID__']),
                'reminder' => route('admin.beauticians.portal.send_reminder', ['id' => $beautician->id, 'booking' => '__ID__']),
                'customerProfile' => route('admin.beauticians.portal.customer_profile', $routeParams),
                'specialistAvailability' => route('admin.beauticians.portal.specialist_availability', $routeParams),
                'manualBookingSlots' => $isPreview
                    ? route('admin.treatment_reservations.manual_bookings.slots')
                    : route('admin.treatment_reservations.portal.manual_bookings.slots'),
            ];
        }

        return [
            'formAction' => route('admin.treatment_reservations.portal'),
            'calendar' => route('admin.treatment_reservations.portal.calendar'),
            'bookingDetails' => route('admin.treatment_reservations.portal.calendar.event', ['booking' => '__ID__']),
            'calendarFullView' => route('admin.treatment_reservations.portal.calendar_page', ['focus' => 1]),
            'updateStatus' => route('admin.treatment_reservations.portal.update_status', ['id' => '__ID__']),
            'updateNotes' => route('admin.treatment_reservations.portal.update_notes', ['id' => '__ID__']),
            'reschedule' => route('admin.treatment_reservations.portal.reschedule', ['id' => '__ID__']),
            'rescheduleSlots' => route('admin.treatment_reservations.portal.reschedule_slots', ['id' => '__ID__']),
            'rescheduleDates' => route('admin.treatment_reservations.portal.reschedule_dates', ['id' => '__ID__']),
            'whatsapp' => route('admin.treatment_reservations.portal.send_whatsapp', ['id' => '__ID__']),
            'consultation' => route('admin.treatment_reservations.portal.consultation', ['id' => '__ID__']),
            'reminder' => route('admin.treatment_reservations.portal.send_reminder', ['id' => '__ID__']),
            'customerProfile' => route('admin.treatment_reservations.portal.customer_profile'),
            'specialistAvailability' => route('admin.treatment_reservations.portal.specialist_availability'),
            'manualBookingSlots' => route('admin.treatment_reservations.portal.manual_bookings.slots'),
        ];
    }


    /**
     * @return array{adminPortalPreview: bool, backUrl: string|null}
     */
    private function portalPreviewContext(Request $request, Beautician $beautician): array
    {
        if (! $this->isAdminBeauticianPreview($request)) {
            return [
                'adminPortalPreview' => false,
                'backUrl' => null,
            ];
        }

        return [
            'adminPortalPreview' => $this->isAdminBeauticianPreview($request, $beautician),
            'backUrl' => $this->isAdminBeauticianPreview($request, $beautician)
                ? route('admin.beauticians.edit', $beautician)
                : null,
        ];
    }


    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function spaBranchesForBeautician(Beautician $beautician, bool $portalLocked = false)
    {
        if (! is_module_enabled('SpaBranch')) {
            return collect();
        }

        $assigned = $beautician->spaBranches;

        if ($assigned->isNotEmpty()) {
            return $assigned->where('is_active', true)->sortBy('position')->pluck('name', 'id');
        }

        if ($portalLocked) {
            return collect();
        }

        return \Modules\SpaBranch\Entities\SpaBranch::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->pluck('name', 'id');
    }



    private function isAdminBeauticianPreview(Request $request, ?Beautician $beautician = null): bool
    {
        $routeName = (string) optional($request->route())->getName();

        if (! str_starts_with($routeName, 'admin.beauticians.portal')) {
            return false;
        }

        $beautician ??= $request->attributes->get('portal_beautician');

        if (! $beautician) {
            return false;
        }

        return (int) auth()->id() !== (int) $beautician->user_id;
    }


    private function bookingIdFromRoute(Request $request, int $fallback): int
    {
        $booking = $request->route('booking');

        return $booking !== null ? (int) $booking : $fallback;
    }
}
