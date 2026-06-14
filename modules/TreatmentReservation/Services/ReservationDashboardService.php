<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Beautician\Entities\Beautician;
use Modules\Support\Money;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Entities\TreatmentBookingActivity;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;
use Modules\TreatmentReservation\Services\BookingCrmInsightService;
use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

class ReservationDashboardService
{
    /**
     * Dashboard stats aligned with the job-sheet kanban columns.
     *
     * @return array{pending: int, inProgress: int, completed: int}
     */
    public function stats(?int $beauticianId = null, ?int $categoryId = null, ?int $spaBranchId = null): array
    {
        $base = $this->filteredBase($beauticianId, $categoryId, $spaBranchId);

        return [
            'pending' => (clone $base)
                ->where('status', TreatmentBooking::STATUS_PENDING)
                ->count(),
            'inProgress' => (clone $base)
                ->where('status', TreatmentBooking::STATUS_IN_PROGRESS)
                ->count(),
            'completed' => (clone $base)
                ->where('status', TreatmentBooking::STATUS_COMPLETED)
                ->count(),
        ];
    }


    /**
     * @return array<string, mixed>
     */
    public function crmPayload(
        ?int $beauticianId = null,
        ?int $categoryId = null,
        ?int $spaBranchId = null,
        string $dateFilter = 'today',
        ?array $urgency = null,
    ): array {
        $filterDate = $this->resolveFilterDate($dateFilter);
        $dateKpis = $this->dateKpis($beauticianId, $categoryId, $spaBranchId, $filterDate);
        $pipeline = $this->pipelineForDate($beauticianId, $categoryId, $spaBranchId, $filterDate);
        $ledger = $this->ledgerAll($beauticianId, $categoryId, $spaBranchId);

        return [
            'dateFilter' => $dateFilter,
            'filterDateLabel' => $this->filterDateLabel($filterDate),
            'filterDateValue' => ($filterDate ?? today())->toDateString(),
            'kpis' => $dateKpis,
            'pipeline' => $pipeline,
            'ledger' => $ledger,
            'ledgerCount' => count($ledger),
            'beauticians' => $this->beauticianRoster($beauticianId, $categoryId, $spaBranchId, $filterDate),
            'alerts' => $this->formatAlerts($urgency),
            'recentActivity' => $this->recentActivity($beauticianId, $categoryId),
            'todayAppointments' => $pipeline['all'] ?? [],
            'needsAttention' => collect($urgency['items'] ?? [])->take(6)->values()->all(),
            'beauticianWorkload' => $this->beauticianWorkload($beauticianId, $categoryId, $spaBranchId, $filterDate),
            'upcomingBookings' => $this->upcomingBookings($beauticianId, $categoryId, $spaBranchId),
        ];
    }


    public function resolveFilterDate(string $filter): ?Carbon
    {
        return match ($filter) {
            'today' => today(),
            'tomorrow' => today()->addDay(),
            'yesterday' => today()->subDay(),
            default => null,
        };
    }


    /**
     * @return array<string, int>
     */
    public function dateKpis(
        ?int $beauticianId,
        ?int $categoryId,
        ?int $spaBranchId,
        ?Carbon $filterDate,
    ): array {
        if ($filterDate === null) {
            $stats = $this->stats($beauticianId, $categoryId, $spaBranchId);
            $pipelineTotal = $this->filteredBase($beauticianId, $categoryId, $spaBranchId)
                ->whereIn('status', [
                    TreatmentBooking::STATUS_PENDING,
                    TreatmentBooking::STATUS_IN_PROGRESS,
                    TreatmentBooking::STATUS_COMPLETED,
                ])
                ->count();

            return [
                'today' => $pipelineTotal,
                'pending' => $stats['pending'],
                'inProgress' => $stats['inProgress'],
                'completed' => $stats['completed'],
            ];
        }

        $base = $this->filteredBase($beauticianId, $categoryId, $spaBranchId)
            ->whereDate('appointment_date', $filterDate);

        return [
            'today' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', TreatmentBooking::STATUS_PENDING)->count(),
            'inProgress' => (clone $base)->where('status', TreatmentBooking::STATUS_IN_PROGRESS)->count(),
            'completed' => (clone $base)->where('status', TreatmentBooking::STATUS_COMPLETED)->count(),
        ];
    }


    /**
     * @return array{
     *     pending: array<int, array<string, mixed>>,
     *     in_progress: array<int, array<string, mixed>>,
     *     completed: array<int, array<string, mixed>>,
     *     all: array<int, array<string, mixed>>
     * }
     */
    public function pipelineForDate(
        ?int $beauticianId,
        ?int $categoryId,
        ?int $spaBranchId,
        ?Carbon $filterDate,
    ): array {
        $query = $this->filteredBase($beauticianId, $categoryId, $spaBranchId)
            ->with(['beautician.files', 'beautician.user', 'beautician.spaBranches', 'product', 'category'])
            ->when($filterDate, fn (Builder $builder) => $builder->whereDate('appointment_date', $filterDate))
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
                TreatmentBooking::STATUS_COMPLETED,
            ])
            ->orderBy('appointment_time');

        $bookings = $query->get()->map(fn (TreatmentBooking $booking) => $this->serializePipelineRow($booking));

        return [
            'pending' => $bookings->where('status', TreatmentBooking::STATUS_PENDING)->values()->all(),
            'in_progress' => $bookings->where('status', TreatmentBooking::STATUS_IN_PROGRESS)->values()->all(),
            'completed' => $bookings->where('status', TreatmentBooking::STATUS_COMPLETED)->values()->all(),
            'all' => $bookings->values()->all(),
        ];
    }


    /**
     * All appointments for the ledger table (not limited by date filter).
     *
     * @return array<int, array<string, mixed>>
     */
    public function ledgerAll(
        ?int $beauticianId,
        ?int $categoryId,
        ?int $spaBranchId,
        int $limit = 100,
    ): array {
        return $this->ledgerBase($beauticianId, $categoryId, $spaBranchId)
            ->with(['beautician.files', 'beautician.user', 'beautician.spaBranches', 'product', 'category', 'order.products.product'])
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time')
            ->limit($limit)
            ->get()
            ->map(fn (TreatmentBooking $booking) => $this->serializeLedgerRow($booking))
            ->all();
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    public function ledgerForDate(
        ?int $beauticianId,
        ?int $categoryId,
        ?int $spaBranchId,
        ?Carbon $filterDate,
        int $limit = 20,
    ): array {
        return $this->filteredBase($beauticianId, $categoryId, $spaBranchId)
            ->with(['beautician', 'product', 'category'])
            ->when($filterDate, fn (Builder $builder) => $builder->whereDate('appointment_date', $filterDate))
            ->orderByDesc('appointment_date')
            ->orderBy('appointment_time')
            ->limit($limit)
            ->get()
            ->map(fn (TreatmentBooking $booking) => $this->serializeLedgerRow($booking))
            ->all();
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    public function beauticianRoster(
        ?int $beauticianId,
        ?int $categoryId,
        ?int $spaBranchId,
        ?Carbon $filterDate,
        int $limit = 8,
    ): array {
        $date = ($filterDate ?? today())->toDateString();

        $query = Beautician::query()
            ->with(['files', 'user'])
            ->where('is_active', true)
            ->when($beauticianId, fn (Builder $builder) => $builder->where('id', $beauticianId))
            ->when(
                $spaBranchId && is_module_enabled('SpaBranch'),
                fn (Builder $builder) => $builder->whereHas(
                    'spaBranches',
                    fn (Builder $branchQuery) => $branchQuery->where('spa_branches.id', $spaBranchId)
                )
            )
            ->orderBy('position')
            ->orderBy('first_name')
            ->limit($limit);

        $inProgressIds = $this->filteredBase($beauticianId, $categoryId, $spaBranchId)
            ->whereDate('appointment_date', $date)
            ->where('status', TreatmentBooking::STATUS_IN_PROGRESS)
            ->whereNotNull('beautician_id')
            ->pluck('beautician_id')
            ->all();

        $pendingIds = $this->filteredBase($beauticianId, $categoryId, $spaBranchId)
            ->whereDate('appointment_date', $date)
            ->where('status', TreatmentBooking::STATUS_PENDING)
            ->whereNotNull('beautician_id')
            ->pluck('beautician_id')
            ->all();

        return $query->get()->map(function (Beautician $beautician) use ($inProgressIds, $pendingIds, $beauticianId, $categoryId, $spaBranchId, $date) {
            $availableToday = ! app(BeauticianAvailabilityService::class)->hasCrmDayOff($beautician->id, $date);

            $status = ! $availableToday
                ? 'unavailable'
                : (in_array($beautician->id, $inProgressIds, true)
                    ? 'with_client'
                    : (in_array($beautician->id, $pendingIds, true) ? 'scheduled' : 'available'));

            $sessionCount = $this->filteredBase($beauticianId, $categoryId, $spaBranchId)
                ->whereDate('appointment_date', $date)
                ->where('beautician_id', $beautician->id)
                ->where('status', TreatmentBooking::STATUS_COMPLETED)
                ->count();

            return [
                'id' => $beautician->id,
                'name' => $beautician->name,
                'job_title' => filled(trim((string) $beautician->job_title)) ? trim((string) $beautician->job_title) : null,
                'color' => $beautician->profile_color ?: '#6d2847',
                'initial' => $beautician->initials,
                'avatar' => $beautician->displayAvatarUrl(),
                'status' => $status,
                'available_today' => $availableToday,
                'session_count' => $sessionCount,
            ];
        })->values()->all();
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    public function formatAlerts(?array $urgency): array
    {
        return collect($urgency['items'] ?? [])
            ->take(5)
            ->map(fn (array $item) => [
                'urgency' => $item['urgency'] ?? 'info',
                'message' => $item['message'] ?? '',
                'customer_name' => $item['customer_name'] ?? '—',
                'time_display' => $item['time_display'] ?? '',
            ])
            ->values()
            ->all();
    }


    public function filterDateLabel(?Carbon $filterDate): string
    {
        if ($filterDate === null) {
            return TrLang::trans('admin.crm.date_all');
        }

        if ($filterDate->isToday()) {
            return TrLang::trans('admin.crm.date_today');
        }

        if ($filterDate->isYesterday()) {
            return TrLang::trans('admin.crm.date_yesterday');
        }

        if ($filterDate->isTomorrow()) {
            return TrLang::trans('admin.crm.date_tomorrow');
        }

        return $filterDate->format('d M Y');
    }


    public function todayCount(?int $beauticianId = null, ?int $categoryId = null, ?int $spaBranchId = null): int
    {
        return $this->filteredBase($beauticianId, $categoryId, $spaBranchId)
            ->whereDate('appointment_date', today())
            ->count();
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    public function todayAppointments(?int $beauticianId = null, ?int $categoryId = null, ?int $spaBranchId = null, int $limit = 8): array
    {
        return $this->filteredBase($beauticianId, $categoryId, $spaBranchId)
            ->with(['beautician', 'product', 'category'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
                TreatmentBooking::STATUS_COMPLETED,
            ])
            ->orderBy('appointment_time')
            ->limit($limit)
            ->get()
            ->map(fn (TreatmentBooking $booking) => $this->serializeAppointmentRow($booking))
            ->all();
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    public function upcomingBookings(?int $beauticianId = null, ?int $categoryId = null, ?int $spaBranchId = null, int $limit = 6): array
    {
        $end = today()->addDays(7)->toDateString();

        return $this->filteredBase($beauticianId, $categoryId, $spaBranchId)
            ->with(['beautician', 'product', 'category'])
            ->whereDate('appointment_date', '>', today())
            ->whereDate('appointment_date', '<=', $end)
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit($limit)
            ->get()
            ->map(fn (TreatmentBooking $booking) => $this->serializeAppointmentRow($booking))
            ->all();
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    public function beauticianWorkload(?int $beauticianId = null, ?int $categoryId = null, ?int $spaBranchId = null, ?Carbon $filterDate = null, int $limit = 6): array
    {
        $date = ($filterDate ?? today())->toDateString();

        $rows = $this->filteredBase($beauticianId, $categoryId, $spaBranchId)
            ->selectRaw('beautician_id, COUNT(*) as booking_total')
            ->whereDate('appointment_date', $date)
            ->whereNotNull('beautician_id')
            ->groupBy('beautician_id')
            ->orderByDesc('booking_total')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $beauticians = Beautician::query()
            ->whereIn('id', $rows->pluck('beautician_id'))
            ->get(['id', 'first_name', 'last_name', 'profile_color'])
            ->keyBy('id');

        return $rows->map(function ($row) use ($beauticians) {
            $beautician = $beauticians[$row->beautician_id] ?? null;

            return [
                'id' => (int) $row->beautician_id,
                'name' => $beautician?->name ?? ('#' . $row->beautician_id),
                'color' => $beautician?->profile_color ?: '#6366f1',
                'initial' => $beautician?->initials ?? '?',
                'booking_count' => (int) $row->booking_total,
            ];
        })->values()->all();
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    public function recentActivity(?int $beauticianId = null, ?int $categoryId = null, int $limit = 8): array
    {
        return TreatmentBookingActivity::query()
            ->with(['user', 'booking.beautician', 'booking.product'])
            ->whereHas('booking', function (Builder $query) use ($beauticianId, $categoryId) {
                $query->withTreatmentProduct()
                    ->whereNot('status', TreatmentBooking::STATUS_CANCELED);

                if ($beauticianId) {
                    $query->where('beautician_id', $beauticianId);
                }

                if ($categoryId) {
                    $query->where('treatment_category_id', $categoryId);
                }
            })
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (TreatmentBookingActivity $activity) => array_merge(
                $activity->toPayload(),
                [
                    'customer_name' => $activity->booking?->customer_full_name ?? '—',
                    'treatment_name' => $activity->booking?->product?->name ?? '—',
                ]
            ))
            ->all();
    }


    /**
     * @return array<string, mixed>
     */
    private function serializePipelineRow(TreatmentBooking $booking): array
    {
        $payload = $booking->toKanbanPayload();

        return app(BookingCrmInsightService::class)->enrichPayload($booking, array_merge($payload, [
            'total_formatted' => Money::inDefaultCurrency($booking->total ?? 0)->format(),
            'payment_status_label' => $booking->paymentStatusLabel(),
            'notes' => $booking->notes,
            'next_status' => match ($booking->status) {
                TreatmentBooking::STATUS_PENDING => TreatmentBooking::STATUS_IN_PROGRESS,
                TreatmentBooking::STATUS_IN_PROGRESS => TreatmentBooking::STATUS_COMPLETED,
                default => null,
            },
        ]));
    }


    /**
     * @return array<string, mixed>
     */
    private function serializeLedgerRow(TreatmentBooking $booking): array
    {
        $booking->loadMissing(['beautician.files', 'beautician.user', 'beautician.spaBranches', 'product', 'category', 'order.products.product']);
        $payload = $booking->toKanbanPayload();
        $treatmentLine = $booking->treatmentLineMeta();

        $initial = strtoupper(mb_substr(trim($booking->customer_first_name ?? ''), 0, 1)
            . mb_substr(trim($booking->customer_last_name ?? ''), 0, 1));

        $treatmentName = $treatmentLine['product_name'] ?? null;
        if (! filled($treatmentName) || $treatmentName === '—') {
            $treatmentName = $booking->product?->name
                ?? $booking->category?->name
                ?? TrLang::trans('admin.crm.ledger_unknown_treatment');
        }

        $treatmentSubtitle = $treatmentLine['treatment_selection']
            ?? $booking->category?->name
            ?? null;

        $beauticianAssigned = $booking->beautician_id !== null && filled($payload['beautician_name'] ?? null) && $payload['beautician_name'] !== '—';

        return app(BookingCrmInsightService::class)->enrichPayload($booking, [
            'id' => $booking->id,
            'status' => $booking->status,
            'status_label' => $this->ledgerStatusLabel($booking->status),
            'status_accent' => TreatmentBooking::statusAccentColor($booking->status),
            'customer_name' => filled($payload['customer_name'] ?? null)
                ? $payload['customer_name']
                : TrLang::trans('admin.crm.ledger_unknown_client'),
            'customer_phone' => $payload['customer_phone'] ?? null,
            'customer_initial' => $initial !== '' ? $initial : '?',
            'customer_color' => $this->customerAccentColor($payload['customer_name'] ?? ''),
            'treatment_name' => $treatmentName,
            'treatment_subtitle' => $treatmentSubtitle,
            'category_name' => $booking->category?->name,
            'appointment_date' => $booking->appointment_date
                ? $booking->appointment_date->format('d M Y')
                : TrLang::trans('admin.crm.ledger_unscheduled'),
            'appointment_time' => filled($booking->formattedAppointmentTime())
                ? $booking->formattedAppointmentTime()
                : TrLang::trans('admin.crm.ledger_time_tbc'),
            'appointment_time_range' => $payload['appointment_time_range'] ?? null,
            'beautician_job_title' => $payload['beautician_job_title'] ?? null,
            'source_label' => $payload['source_label'] ?? null,
            'spa_branch_name' => $payload['spa_branch_name'] ?? null,
            'payment_status_label' => $payload['payment_status_label'] ?? null,
            'beautician_name' => $beauticianAssigned
                ? $payload['beautician_name']
                : TrLang::trans('admin.crm.ledger_unassigned'),
            'beautician_assigned' => $beauticianAssigned,
            'beautician_color' => $payload['beautician_color'] ?? '#6d2847',
            'beautician_avatar' => $payload['beautician_avatar'] ?? null,
            'beautician_initial' => $beauticianAssigned ? ($payload['beautician_initial'] ?? '?') : '?',
            'total_formatted' => $booking->ledgerLineTotal()->format(),
            'notes' => $payload['notes'] ?? null,
            'order_url' => $payload['order_url'] ?? null,
            'can_edit_manual' => $payload['can_edit_manual'] ?? false,
            'can_reschedule_manual' => $payload['can_reschedule_manual'] ?? false,
        ]);
    }


    private function ledgerStatusLabel(string $status): string
    {
        if ($status === TreatmentBooking::STATUS_CANCELED) {
            return TrLang::trans('admin.crm.status_canceled');
        }

        return TrLang::trans('admin.kanban.' . $status);
    }


    private function customerAccentColor(string $name): string
    {
        $palette = ['#4a0e2e', '#6d28d9', '#0284c7', '#047857', '#c2410c', '#be123c', '#7c3aed', '#0f766e'];

        if (trim($name) === '') {
            return $palette[0];
        }

        return $palette[abs(crc32(mb_strtolower(trim($name)))) % count($palette)];
    }


    private function ledgerBase(?int $beauticianId = null, ?int $categoryId = null, ?int $spaBranchId = null): Builder
    {
        return TreatmentBooking::query()
            ->when($beauticianId, fn (Builder $query) => $query->where('beautician_id', $beauticianId))
            ->when($categoryId, fn (Builder $query) => $query->where('treatment_category_id', $categoryId))
            ->when(
                $spaBranchId && is_module_enabled('SpaBranch'),
                fn (Builder $query) => $query->whereHas(
                    'beautician.spaBranches',
                    fn (Builder $branchQuery) => $branchQuery->where('spa_branches.id', $spaBranchId)
                )
            );
    }


    /**
     * @return array<string, mixed>
     */
    private function serializeAppointmentRow(TreatmentBooking $booking): array
    {
        $payload = $booking->toKanbanPayload();

        return [
            'id' => $payload['id'],
            'status' => $payload['status'],
            'customer_name' => $payload['customer_name'],
            'customer_phone' => $payload['customer_phone'] ?? null,
            'treatment_name' => $payload['treatment_name'] ?? $payload['product_name'] ?? '—',
            'appointment_time' => $payload['appointment_time'] ?? $payload['time'] ?? '—',
            'appointment_time_range' => $payload['appointment_time_range'] ?? null,
            'appointment_end_time' => $payload['appointment_end_time'] ?? null,
            'slot_duration_minutes' => $payload['slot_duration_minutes'] ?? null,
            'appointment_date' => $booking->appointment_date?->format('d M Y'),
            'appointment_date_value' => $booking->appointment_date?->format('Y-m-d'),
            'date' => $booking->appointment_date?->format('Y-m-d'),
            'beautician_name' => $payload['beautician_name'] ?? '—',
            'beautician_job_title' => $payload['beautician_job_title'] ?? null,
            'beautician_color' => $payload['beautician_color'] ?? '#6366f1',
            'beautician_initial' => $payload['beautician_initial'] ?? '?',
            'category_name' => $payload['category_name'] ?? null,
            'source' => $payload['source'] ?? null,
            'source_label' => $payload['source_label'] ?? null,
            'spa_branch_name' => $payload['spa_branch_name'] ?? null,
            'payment_status_label' => $payload['payment_status_label'] ?? null,
            'status_accent' => TreatmentBooking::statusAccentColor($payload['status']),
        ];
    }


    private function filteredBase(?int $beauticianId = null, ?int $categoryId = null, ?int $spaBranchId = null): Builder
    {
        return TreatmentBooking::query()
            ->withTreatmentProduct()
            ->whereNot('status', TreatmentBooking::STATUS_CANCELED)
            ->when($beauticianId, fn (Builder $query) => $query->where('beautician_id', $beauticianId))
            ->when($categoryId, fn (Builder $query) => $query->where('treatment_category_id', $categoryId))
            ->when(
                $spaBranchId && is_module_enabled('SpaBranch'),
                fn (Builder $query) => $query->whereHas(
                    'beautician.spaBranches',
                    fn (Builder $branchQuery) => $branchQuery->where('spa_branches.id', $spaBranchId)
                )
            );
    }


    public function statsForBeautician(int $beauticianId): array
    {
        $today = today()->toDateString();
        $weekEnd = Carbon::parse($today)->addDays(7)->toDateString();
        $weekStart = Carbon::now()->startOfWeek()->toDateString();
        $weekEndDate = Carbon::now()->endOfWeek()->toDateString();

        return [
            'totalBookings' => $this->beauticianBookingBase($beauticianId)->count(),
            'todayCompleted' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_COMPLETED)
                ->whereDate('appointment_date', $today)
                ->count(),
            'weekCompleted' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_COMPLETED)
                ->whereDate('appointment_date', '>=', $weekStart)
                ->whereDate('appointment_date', '<=', $weekEndDate)
                ->count(),
            'pendingToday' => $this->beauticianBookingBase($beauticianId)
                ->whereDate('appointment_date', $today)
                ->where('status', TreatmentBooking::STATUS_PENDING)
                ->count(),
            'inProgress' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_IN_PROGRESS)
                ->count(),
            'treatmentRevenue' => Money::inDefaultCurrency(
                $this->beauticianBookingBase($beauticianId)
                    ->where('status', TreatmentBooking::STATUS_COMPLETED)
                    ->sum('total')
            ),
            'upcomingWeek' => $this->beauticianBookingBase($beauticianId)
                ->whereBetween('appointment_date', [$today, $weekEnd])
                ->whereIn('status', [
                    TreatmentBooking::STATUS_PENDING,
                    TreatmentBooking::STATUS_IN_PROGRESS,
                ])
                ->count(),
        ];
    }


    /**
     * Stats aligned with the beautician job-sheet kanban columns.
     *
     * @return array{pending: int, inProgress: int, completed: int}
     */
    public function statsForBeauticianSchedule(int $beauticianId): array
    {
        return [
            'pending' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_PENDING)
                ->count(),
            'inProgress' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_IN_PROGRESS)
                ->count(),
            'completed' => $this->beauticianBookingBase($beauticianId)
                ->where('status', TreatmentBooking::STATUS_COMPLETED)
                ->count(),
        ];
    }


    /**
     * @return \Illuminate\Support\Collection<int, TreatmentBooking>
     */
    public function todayAppointmentsForBeautician(int $beauticianId)
    {
        return $this->beauticianBookingBase($beauticianId)
            ->with(['product', 'category'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->orderBy('appointment_time')
            ->get();
    }


    private function beauticianBookingBase(int $beauticianId)
    {
        return TreatmentBooking::query()
            ->withTreatmentProduct()
            ->where('beautician_id', $beauticianId)
            ->whereNot('status', TreatmentBooking::STATUS_CANCELED);
    }
}
