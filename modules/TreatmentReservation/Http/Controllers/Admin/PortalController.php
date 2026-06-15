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
use Modules\TreatmentReservation\Services\BookingCustomerWhatsAppService;
use Modules\TreatmentReservation\Services\BookingJobSheetOrderSync;
use Modules\TreatmentReservation\Services\CustomerAppointmentReminderService;
use Modules\TreatmentReservation\Services\CustomerCrmProfileService;
use Modules\TreatmentReservation\Services\ManualBookingProductCatalogService;
use Modules\TreatmentReservation\Services\ReservationDashboardService;
use Modules\TreatmentReservation\Services\TreatmentBookingActivityLogger;
use Modules\TreatmentReservation\Services\TreatmentReservationAnalyticsService;
use Modules\TreatmentReservation\Services\UpcomingJobUrgencyService;
use Modules\User\Services\OneSenderWhatsAppService;

class PortalController extends Controller
{
    public function __construct(
        private ReservationDashboardService $dashboard,
        private TreatmentReservationAnalyticsService $analytics,
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
        $analyticsDays = TreatmentReservationAnalyticsService::DEFAULT_DAYS;
        $urgencyPayload = $this->urgency->forBeautician($beauticianId);

        $crmRoutes = $this->crmApiRoutes($request, $beautician);

        return view('treatmentreservation::admin.portal.dashboard', array_merge([
            'beautician' => $beautician,
            'activeView' => 'dashboard',
            'stats' => $this->dashboard->stats($beauticianId, $categoryId, $spaBranchId),
            'dashboardData' => $this->dashboard->crmPayload(
                $beauticianId,
                $categoryId,
                $spaBranchId,
                $dateFilter,
                $urgencyPayload,
                $customFilterDate,
            ),
            'urgency' => $urgencyPayload,
            'analytics' => $this->analytics->overview($analyticsDays, $beauticianId),
            'analyticsCharts' => $this->analytics->chartPayload($analyticsDays, $beauticianId),
            'categories' => TreatmentCategory::active()->ordered()->get(),
            'spaBranches' => $this->spaBranchesForBeautician($beautician, $lockPortalFilters),
            'manualBookingProductCatalog' => app(ManualBookingProductCatalogService::class)->catalog(),
            'beauticianPickerOptions' => Beautician::activeListForCheckout(),
            'filters' => array_merge($filters, [
                'beautician_id' => $beauticianId,
                'month' => $request->input('month', now()->format('Y-m')),
            ]),
            'portalFilterContext' => $this->portalFilterContext($beautician, $filters, $lockPortalFilters),
            'crmRoutes' => $crmRoutes,
            'crmCanEdit' => true,
            'crmCanCreate' => ! $this->isAdminBeauticianPreview($request)
                && (auth()->user()?->hasAccess('admin.treatment_reservations.portal.create') ?? false),
            'crmSpecialistProfileUrl' => $this->isAdminBeauticianPreview($request)
                ? route('admin.beauticians.portal.availability', $beautician->id)
                : route('admin.treatment_reservations.portal.availability'),
            'portalDashboard' => true,
        ], $this->portalPreviewContext($request, $beautician)));
    }


    public function jobSheet(Request $request)
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $todayAppointments = $this->dashboard->todayAppointmentsForBeautician($beautician->id);

        $activeView = in_array($request->query('view'), ['kanban', 'calendar'], true)
            ? $request->query('view')
            : 'kanban';

        $calendarFocus = $request->boolean('focus') && $activeView === 'calendar';

        $portalContext = $this->portalContext($request, $beautician);

        return view('treatmentreservation::admin.portal.job_sheet', array_merge([
            'beautician' => $beautician,
            'stats' => $this->dashboard->statsForBeauticianSchedule($beautician->id),
            'performanceStats' => $this->dashboard->statsForBeautician($beautician->id),
            'todayAppointments' => $todayAppointments,
            'todayBookingsPayload' => $todayAppointments->map->toKanbanPayload()->values(),
            'activeView' => $activeView,
            'calendarFocus' => $calendarFocus,
            'manualBookingProductCatalog' => app(ManualBookingProductCatalogService::class)->catalog(),
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
                    'kanban' => route('admin.treatment_reservations.portal.kanban'),
                    'update_status' => route('admin.treatment_reservations.portal.update_status', ['id' => '__ID__']),
                    'update_notes' => route('admin.treatment_reservations.portal.update_notes', ['id' => '__ID__']),
                    'send_whatsapp' => route('admin.treatment_reservations.portal.send_whatsapp', ['id' => '__ID__']),
                ],
                'backUrl' => null,
            ];
        }

        $routeParams = ['id' => $beautician->id];

        return [
            'adminPortalPreview' => true,
            'portalApiRoutes' => [
                'calendar' => route('admin.beauticians.portal.calendar', $routeParams),
                'kanban' => route('admin.beauticians.portal.kanban', $routeParams),
                'update_status' => route('admin.beauticians.portal.update_status', ['id' => $beautician->id, 'booking' => '__ID__']),
                'update_notes' => route('admin.beauticians.portal.update_notes', ['id' => $beautician->id, 'booking' => '__ID__']),
                'send_whatsapp' => route('admin.beauticians.portal.send_whatsapp', ['id' => $beautician->id, 'booking' => '__ID__']),
            ],
            'backUrl' => route('admin.beauticians.edit', $beautician),
        ];
    }


    public function calendarEvents(Request $request): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $bookings = TreatmentBooking::query()
            ->forCalendar($request->input('month'), $beautician->id)
            ->get()
            ->map->toCalendarPayload();

        return response()->json(['bookings' => $bookings]);
    }


    public function kanbanBoard(Request $request): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $bookings = TreatmentBooking::query()
            ->forKanban($beautician->id, $request->integer('treatment_category_id') ?: null)
            ->get();

        $columns = [];

        foreach (TreatmentBooking::kanbanStatuses() as $status) {
            $columns[$status] = $bookings
                ->where('status', $status)
                ->values()
                ->map->toKanbanPayload();
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


    public function updateBeauticianNotes(Request $request, int $id): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'beautician_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $booking = TreatmentBooking::query()
            ->where('beautician_id', $beautician->id)
            ->findOrFail($this->bookingIdFromRoute($request, $id));

        $previousNotes = $booking->beautician_notes;
        $booking->update([
            'beautician_notes' => $request->input('beautician_notes'),
        ]);

        app(TreatmentBookingActivityLogger::class)->logBeauticianNotes(
            $booking,
            $previousNotes,
            $request->input('beautician_notes')
        );

        $freshBooking = $booking->fresh();

        return response()->json([
            'booking' => $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload()),
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
            'booking_id' => ['nullable', 'integer'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        try {
            if ($request->filled('booking_id')) {
                $booking = TreatmentBooking::query()
                    ->where('beautician_id', $beautician->id)
                    ->findOrFail($request->integer('booking_id'));

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

        return [
            'date_filter' => $dateFilter,
            'filter_date' => $customFilterDate,
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
        if ($this->isAdminBeauticianPreview($request)) {
            $routeParams = ['id' => $beautician->id];

            return [
                'formAction' => route('admin.beauticians.portal.dashboard', $routeParams),
                'calendar' => route('admin.beauticians.portal.calendar', $routeParams),
                'updateStatus' => route('admin.beauticians.portal.update_status', ['id' => $beautician->id, 'booking' => '__ID__']),
                'whatsapp' => route('admin.beauticians.portal.send_whatsapp', ['id' => $beautician->id, 'booking' => '__ID__']),
                'reminder' => route('admin.beauticians.portal.send_reminder', ['id' => $beautician->id, 'booking' => '__ID__']),
                'customerProfile' => route('admin.beauticians.portal.customer_profile', $routeParams),
                'specialistAvailability' => route('admin.beauticians.portal.specialist_availability', $routeParams),
                'manualBookingSlots' => route('admin.treatment_reservations.manual_bookings.slots'),
                'manualBookingCustomers' => route('admin.treatment_reservations.manual_bookings.customers'),
                'manualBookingStore' => route('admin.treatment_reservations.manual_bookings.store'),
                'manualBookingUpdate' => route('admin.treatment_reservations.manual_bookings.update', ['booking' => '__ID__']),
                'manualBookingCancel' => route('admin.treatment_reservations.manual_bookings.cancel', ['booking' => '__ID__']),
            ];
        }

        return [
            'formAction' => route('admin.treatment_reservations.portal.dashboard'),
            'calendar' => route('admin.treatment_reservations.portal.calendar'),
            'updateStatus' => route('admin.treatment_reservations.portal.update_status', ['id' => '__ID__']),
            'whatsapp' => route('admin.treatment_reservations.portal.send_whatsapp', ['id' => '__ID__']),
            'reminder' => route('admin.treatment_reservations.portal.send_reminder', ['id' => '__ID__']),
            'customerProfile' => route('admin.treatment_reservations.portal.customer_profile'),
            'specialistAvailability' => route('admin.treatment_reservations.portal.specialist_availability'),
            'manualBookingSlots' => route('admin.treatment_reservations.portal.manual_bookings.slots'),
            'manualBookingCustomers' => route('admin.treatment_reservations.portal.manual_bookings.customers'),
            'manualBookingStore' => route('admin.treatment_reservations.portal.manual_bookings.store'),
            'manualBookingUpdate' => route('admin.treatment_reservations.portal.manual_bookings.update', ['booking' => '__ID__']),
            'manualBookingCancel' => route('admin.treatment_reservations.portal.manual_bookings.cancel', ['booking' => '__ID__']),
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
            'adminPortalPreview' => true,
            'backUrl' => route('admin.beauticians.edit', $beautician),
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


    private function isAdminBeauticianPreview(Request $request): bool
    {
        $routeName = (string) optional($request->route())->getName();

        return str_starts_with($routeName, 'admin.beauticians.portal.');
    }


    private function bookingIdFromRoute(Request $request, int $fallback): int
    {
        $booking = $request->route('booking');

        return $booking !== null ? (int) $booking : $fallback;
    }
}
