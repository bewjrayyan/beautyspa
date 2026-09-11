<?php

declare(strict_types=1);

namespace Modules\Lead\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Beautician\Entities\Beautician;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\BookingCheckinPassService;

/**
 * Live check-in board derived from treatment bookings.
 * Arrival and treatment are separate states: checked_in_at records arrival,
 * while status in_progress records that the treatment session has started.
 */
final class CentralCheckinService
{
    public function __construct(
        private readonly BookingCheckinPassService $checkinPasses,
    ) {
    }


    /**
     * @param  array{
     *     q?:string|null,
     *     status?:string|null,
     *     branch?:int|string|null,
     *     beautician?:int|string|null,
     *     date?:string|null,
     *     scope?:string|null,
     *     per_page?:int
     * }  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 25)));

        return $this->baseQuery($filters)
            ->with([
                'product',
                'beautician:id,first_name,last_name',
                'order:id,payment_status,spa_branch_id',
                'customer:id,first_name,last_name,email,phone',
            ])
            ->orderByRaw("CASE status WHEN 'in_progress' THEN 0 WHEN 'pending' THEN 1 WHEN 'completed' THEN 2 ELSE 3 END")
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     live:int,
     *     waiting:int,
     *     in_treatment:int,
     *     completed:int,
     *     unpaid:int,
     *     avg_wait_mins:int
     * }
     */
    public function summary(array $filters = []): array
    {
        // Apply the same date/scope/search/assignment filters as the list, excluding its tab.
        $base = $this->baseQuery(array_replace($filters, ['status' => 'all']));
        $counts = (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $waiting = (clone $base)
            ->where('status', TreatmentBooking::STATUS_PENDING)
            ->whereNotNull('checked_in_at')
            ->count();
        $inTreatment = (int) ($counts[TreatmentBooking::STATUS_IN_PROGRESS] ?? 0);
        $completed = (int) ($counts[TreatmentBooking::STATUS_COMPLETED] ?? 0);
        $unpaid = $waitTotal = $waitCount = 0;

        foreach ((clone $base)->whereIn('status', [TreatmentBooking::STATUS_PENDING, TreatmentBooking::STATUS_IN_PROGRESS])
            ->with('order')->orderBy('id')->lazy(200) as $booking) {
            if ($booking->hasOutstandingPayment()) {
                $unpaid++;
            }
            if ($booking->status === TreatmentBooking::STATUS_PENDING && $booking->checked_in_at && ($mins = $this->waitingMinutes($booking)) !== null) {
                $waitTotal += $mins;
                $waitCount++;
            }
        }

        return [
            'live' => $waiting + $inTreatment,
            'waiting' => $waiting,
            'in_treatment' => $inTreatment,
            'completed' => $completed,
            'unpaid' => $unpaid,
            'avg_wait_mins' => $waitCount > 0 ? (int) round($waitTotal / $waitCount) : 0,
        ];
    }

    /**
     * @return list<array{value:string,label:string}>
     */
    public function statusOptions(): array
    {
        return [
            ['value' => 'live', 'label' => trans('lead::central.checkin.tab_live')],
            ['value' => 'waiting', 'label' => trans('lead::central.checkin.tab_waiting')],
            ['value' => 'in_progress', 'label' => trans('lead::central.checkin.tab_treatment')],
            ['value' => 'completed', 'label' => trans('lead::central.checkin.tab_completed')],
            ['value' => 'all', 'label' => trans('lead::central.checkin.tab_all')],
        ];
    }

    /**
     * @return list<array{id:int,name:string}>
     */
    public function beauticianOptions(): array
    {
        return Beautician::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(static fn (Beautician $b) => [
                'id' => (int) $b->id,
                'name' => trim($b->first_name.' '.$b->last_name) ?: ('#'.$b->id),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id:int,code:string,name:string}>
     */
    public function branchOptions(): array
    {
        return SpaBranch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(static fn (SpaBranch $b) => [
                'id' => (int) $b->id,
                'code' => (string) ($b->code ?: ''),
                'name' => (string) $b->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(TreatmentBooking $booking): array
    {
        $name = trim((string) $booking->customer_full_name);
        if ($name === '') {
            $name = (string) ($booking->customer_phone ?: trans('lead::central.checkin.guest'));
        }

        $branch = null;
        $branchId = (int) ($booking->spa_branch_id ?: $booking->order?->spa_branch_id ?: 0);
        if ($branchId > 0) {
            $branch = SpaBranch::query()->find($branchId, ['id', 'code', 'name']);
        }

        $payment = $booking->resolvedPaymentStatus();
        $paid = ! $booking->hasOutstandingPayment();
        $wait = $this->waitingMinutes($booking);
        $checkedIn = $booking->checked_in_at;
        $beau = trim((string) (($booking->beautician?->first_name ?? '').' '.($booking->beautician?->last_name ?? '')));

        return [
            'id' => (int) $booking->id,
            'code' => $booking->referenceCode(),
            'customer_id' => $booking->customer_id ? (int) $booking->customer_id : null,
            'name' => $name,
            'phone' => (string) ($booking->customer_phone ?: ''),
            'email' => (string) ($booking->customer_email ?: ''),
            'initial' => mb_strtoupper(mb_substr($name !== '' ? $name : 'C', 0, 1)),
            'treatment' => (string) ($booking->product?->name ?: '—'),
            'beautician' => $beau !== '' ? $beau : '—',
            'beautician_id' => $booking->beautician_id ? (int) $booking->beautician_id : null,
            'branch' => $branch?->code ?: ($branch?->name ?: '—'),
            'branch_name' => $branch?->name ?: '—',
            'branch_id' => $branchId > 0 ? $branchId : null,
            'date' => optional($booking->appointment_date)?->format('Y-m-d'),
            'date_label' => optional($booking->appointment_date)?->format('d M Y') ?: '—',
            'time' => (string) ($booking->displayAppointmentTime() ?: $booking->appointment_time ?: '—'),
            'status' => (string) $booking->status,
            'status_label' => $booking->treatmentStatusLabel(),
            'payment_status' => $payment,
            'payment_label' => $booking->paymentStatusLabel(),
            'payment_ok' => $paid,
            'order_id' => $booking->order_id ? (int) $booking->order_id : null,
            'checkin_pass_url' => $this->checkinPasses->url($booking),
            'arrival_state' => $booking->checked_in_at ? 'waiting' : 'booked',
            'arrival_label' => $booking->checked_in_at ? trans('lead::central.checkin.arrival_waiting') : trans('lead::central.checkin.arrival_booked'),
            'waiting_mins' => $wait,
            'waiting_label' => $wait === null ? '—' : trans('lead::central.checkin.mins', ['count' => $wait]),
            'checked_in_at' => optional($checkedIn)?->toDateTimeString(),
            'checked_in_label' => optional($checkedIn)?->format('H:i') ?: '—',
            'clearance' => $this->clearanceState($booking),
            'clearance_label' => $this->clearanceLabel($booking),
        ];
    }

    public function clearanceState(TreatmentBooking $booking): string
    {
        if ($booking->status === TreatmentBooking::STATUS_COMPLETED) {
            return 'done';
        }
        if ($booking->status === TreatmentBooking::STATUS_IN_PROGRESS) {
            return 'in_treatment';
        }
        if ($booking->hasOutstandingPayment()) {
            return 'blocked';
        }
        if ($booking->status === TreatmentBooking::STATUS_PENDING) {
            return 'waiting';
        }

        return 'other';
    }

    public function clearanceLabel(TreatmentBooking $booking): string
    {
        return match ($this->clearanceState($booking)) {
            'done' => trans('lead::central.clearance.state_done'),
            'in_treatment' => trans('lead::central.clearance.state_treatment'),
            'blocked' => trans('lead::central.clearance.state_blocked'),
            'waiting' => trans('lead::central.clearance.state_waiting'),
            default => trans('lead::central.clearance.state_other'),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function baseQuery(array $filters): Builder
    {
        $query = TreatmentBooking::query()->where('status', '!=', TreatmentBooking::STATUS_CANCELED);

        $scope = (string) ($filters['scope'] ?? 'day');
        $date = $this->resolveDate($filters['date'] ?? null);

        if ($scope === 'pipeline') {
            $query->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ]);
        } else {
            $query->whereDate('appointment_date', $date->toDateString());
        }

        $status = (string) ($filters['status'] ?? 'live');
        if ($status === 'live') {
            $query->where(function (Builder $live): void {
                $live->where('status', TreatmentBooking::STATUS_IN_PROGRESS)
                    ->orWhere(function (Builder $waiting): void {
                        $waiting->where('status', TreatmentBooking::STATUS_PENDING)
                            ->whereNotNull('checked_in_at');
                    });
            });
        } elseif ($status === 'waiting') {
            $query->where('status', TreatmentBooking::STATUS_PENDING)
                ->whereNotNull('checked_in_at');
        } elseif ($status === 'in_progress') {
            $query->where('status', TreatmentBooking::STATUS_IN_PROGRESS);
        } elseif ($status === 'completed') {
            $query->where('status', TreatmentBooking::STATUS_COMPLETED);
        }

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function (Builder $inner) use ($q, $like): void {
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q);
                }
                $inner->orWhere('customer_first_name', 'like', $like)
                    ->orWhere('customer_last_name', 'like', $like)
                    ->orWhere('customer_phone', 'like', $like)
                    ->orWhere('customer_email', 'like', $like);
            });
        }

        $branchId = $this->positiveIntOrNull($filters['branch'] ?? null);
        if ($branchId !== null) {
            $query->where(function (Builder $branch) use ($branchId): void {
                $branch->where('spa_branch_id', $branchId)
                    ->orWhere(function (Builder $fallback) use ($branchId): void {
                        $fallback->whereNull('spa_branch_id')
                            ->whereHas('order', fn (Builder $order): Builder => $order->where('spa_branch_id', $branchId));
                    });
            });
        }

        $beauticianId = $this->positiveIntOrNull($filters['beautician'] ?? null);
        if ($beauticianId !== null) {
            $query->where('beautician_id', $beauticianId);
        }

        return $query;
    }

    private function waitingMinutes(TreatmentBooking $booking): ?int
    {
        if ($booking->status === TreatmentBooking::STATUS_IN_PROGRESS) {
            $started = $booking->sessionStartedAt();
            if ($started) {
                return max(0, (int) $started->diffInMinutes(now()));
            }
        }

        if ($booking->status !== TreatmentBooking::STATUS_PENDING) {
            return null;
        }

        if (! $booking->checked_in_at) {
            return null;
        }

        return max(0, (int) $booking->checked_in_at->diffInMinutes(now()));
    }

    private function resolveDate(mixed $value): Carbon
    {
        try {
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return Carbon::parse($value)->startOfDay();
            }
        } catch (\Throwable) {
        }

        return Carbon::today();
    }

    private function positiveIntOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === 'all') {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }
}
