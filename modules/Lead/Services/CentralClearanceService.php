<?php

declare(strict_types=1);

namespace Modules\Lead\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

/**
 * HQ clearance desk: paid+pending = waiting; unpaid active = blocked; in_progress = in treatment.
 */
final class CentralClearanceService
{
    public function __construct(
        private readonly CentralCheckinService $checkin,
    ) {
    }

    /**
     * @param  array{
     *     q?:string|null,
     *     state?:string|null,
     *     branch?:int|string|null,
     *     beautician?:int|string|null,
     *     per_page?:int
     * }  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 25)));
        $page = max(1, (int) request()->query('page', 1));
        $state = (string) ($filters['state'] ?? 'waiting');

        $query = $this->baseQuery($filters)
            ->with([
                'product',
                'beautician:id,first_name,last_name',
                'order:id,payment_status,spa_branch_id',
                'customer:id,first_name,last_name,email,phone',
            ])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'in_progress' THEN 1 ELSE 2 END")
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');

        if (in_array($state, ['waiting', 'blocked', 'all_queue'], true)) {
            // Keep model payment semantics (manual vs order) without truncating the queue.
            $total = 0;
            $slice = collect();
            $offset = ($page - 1) * $perPage;
            foreach ($query->orderBy('id')->lazy(200) as $booking) {
                $clearance = $this->checkin->clearanceState($booking);
                $matches = match ($state) {
                    'waiting' => $clearance === 'waiting',
                    'blocked' => $clearance === 'blocked',
                    default => in_array($clearance, ['waiting', 'blocked', 'in_treatment'], true),
                };
                if (! $matches) {
                    continue;
                }
                if ($total >= $offset && $slice->count() < $perPage) {
                    $slice->push($booking);
                }
                $total++;
            }

            return new Paginator(
                $slice,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     waiting:int,
     *     blocked:int,
     *     in_treatment:int,
     *     done_today:int,
     *     queue:int
     * }
     */
    public function summary(array $filters = []): array
    {
        $active = $this->baseQuery(array_replace($filters, ['state' => 'all_queue']))
            ->with('order')->orderBy('id')->lazy(200);

        $waiting = 0;
        $blocked = 0;
        $inTreatment = 0;
        foreach ($active as $booking) {
            $clearance = $this->checkin->clearanceState($booking);
            if ($clearance === 'waiting') {
                $waiting++;
            } elseif ($clearance === 'blocked') {
                $blocked++;
            } elseif ($clearance === 'in_treatment') {
                $inTreatment++;
            }
        }

        $doneToday = $this->baseQuery(array_replace($filters, ['state' => 'done']))->count();

        return [
            'waiting' => $waiting,
            'blocked' => $blocked,
            'in_treatment' => $inTreatment,
            'done_today' => $doneToday,
            'queue' => $waiting + $blocked + $inTreatment,
        ];
    }

    /**
     * @return list<array{value:string,label:string}>
     */
    public function stateOptions(): array
    {
        return [
            ['value' => 'waiting', 'label' => trans('lead::central.clearance.tab_waiting')],
            ['value' => 'blocked', 'label' => trans('lead::central.clearance.tab_blocked')],
            ['value' => 'in_treatment', 'label' => trans('lead::central.clearance.tab_treatment')],
            ['value' => 'all_queue', 'label' => trans('lead::central.clearance.tab_queue')],
            ['value' => 'done', 'label' => trans('lead::central.clearance.tab_done')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(TreatmentBooking $booking): array
    {
        return $this->checkin->toArray($booking);
    }

    public function beauticianOptions(): array
    {
        return $this->checkin->beauticianOptions();
    }

    public function branchOptions(): array
    {
        return $this->checkin->branchOptions();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function baseQuery(array $filters): Builder
    {
        $state = (string) ($filters['state'] ?? 'waiting');

        $query = TreatmentBooking::query()->where('status', '!=', TreatmentBooking::STATUS_CANCELED);

        if ($state === 'done') {
            $query->where('status', TreatmentBooking::STATUS_COMPLETED)
                ->whereHas('activities', static function (Builder $activity): void {
                    $activity->where('action', 'status_changed')
                        ->where('to_value', TreatmentBooking::STATUS_COMPLETED)
                        ->whereDate('created_at', now()->toDateString());
                });
        } elseif ($state === 'in_treatment') {
            $query->where('status', TreatmentBooking::STATUS_IN_PROGRESS);
        } else {
            $query->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ]);
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
            $query->where('spa_branch_id', $branchId);
        }

        $beauticianId = $this->positiveIntOrNull($filters['beautician'] ?? null);
        if ($beauticianId !== null) {
            $query->where('beautician_id', $beauticianId);
        }

        return $query;
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
