<?php

declare(strict_types=1);

namespace Modules\Lead\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Beautician\Entities\Beautician;
use Modules\Lead\Entities\Lead;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

final class LeadWorkspaceService
{
    /**
     * @param  array{q?:string|null,status?:string|null,branch?:int|string|null,beautician?:int|string|null,month?:string|null,per_page?:int}  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 25)));

        $query = Lead::query()
            ->with(['spaBranch:id,name,code', 'beautician:id,first_name,last_name', 'customer:id,first_name,last_name,phone'])
            ->search($filters['q'] ?? null)
            ->status($filters['status'] ?? null)
            ->latest('id');

        $branchId = $this->positiveIntOrNull($filters['branch'] ?? null);
        if ($branchId !== null) {
            $query->where('spa_branch_id', $branchId);
        }

        $beauticianId = $this->positiveIntOrNull($filters['beautician'] ?? null);
        if ($beauticianId !== null) {
            $query->where('beautician_id', $beauticianId);
        }

        [$from, $to] = $this->resolveMonthRange($filters['month'] ?? null);
        if ($from !== null && $to !== null) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return array{
     *     raw:int,
     *     unique:int,
     *     duplicates:int,
     *     existing:int,
     *     converted:int,
     *     conversion_pct:float
     * }
     */
    public function summary(?int $branchId = null, ?string $month = null): array
    {
        $base = Lead::query();
        if ($branchId !== null) {
            $base->where('spa_branch_id', $branchId);
        }

        [$from, $to] = $this->resolveMonthRange($month);
        if ($from !== null && $to !== null) {
            $base->whereBetween('created_at', [$from, $to]);
        }

        $raw = (clone $base)->count();
        $duplicates = (clone $base)->where('is_duplicate', true)->count();
        $unique = max(0, $raw - $duplicates);
        $existing = (clone $base)->where('is_existing_customer', true)->count();
        $converted = (clone $base)->where('status', Lead::STATUS_CONVERTED)->count();
        $conversionPct = $unique > 0 ? round(($converted / $unique) * 100, 1) : 0.0;

        return [
            'raw' => $raw,
            'unique' => $unique,
            'duplicates' => $duplicates,
            'existing' => $existing,
            'converted' => $converted,
            'conversion_pct' => $conversionPct,
        ];
    }

    /**
     * @param  array{
     *     name:string,
     *     phone:string,
     *     email?:string|null,
     *     source?:string|null,
     *     status?:string|null,
     *     spa_branch_id?:int|null,
     *     beautician_id?:int|null,
     *     lead_import_id?:int|null
     * }  $data
     */
    public function create(array $data): Lead
    {
        $phone = PhoneNumber::normalize($data['phone']);
        $customer = $phone !== '' ? User::findByPhone($phone) : null;
        $isDuplicate = $phone !== '' && Lead::query()->where('phone', $phone)->exists();

        $lead = new Lead([
            'name' => $data['name'],
            'phone' => $phone,
            'email' => $data['email'] ?? null,
            'source' => $data['source'] ?? 'manual',
            'status' => $data['status'] ?? Lead::STATUS_NEW,
            'spa_branch_id' => $data['spa_branch_id'] ?? null,
            'beautician_id' => $data['beautician_id'] ?? null,
            'customer_id' => $customer?->id,
            'lead_import_id' => $data['lead_import_id'] ?? null,
            'is_duplicate' => $isDuplicate,
            'is_existing_customer' => $customer !== null,
        ]);
        $lead->save();

        return $lead->fresh(['spaBranch', 'beautician', 'customer']) ?? $lead;
    }

    public function updateStatus(Lead $lead, string $status): Lead
    {
        $lead->status = $status;

        if ($status === Lead::STATUS_FOLLOW_UP) {
            $lead->last_followed_up_at = now();
        }

        $lead->save();

        return $lead->fresh(['spaBranch', 'beautician', 'customer']) ?? $lead;
    }

    /**
     * @param  array{
     *     name:string,
     *     phone:string,
     *     email?:string|null,
     *     source?:string|null,
     *     status?:string|null,
     *     spa_branch_id?:int|null,
     *     beautician_id?:int|null
     * }  $data
     */
    public function update(Lead $lead, array $data): Lead
    {
        $phone = PhoneNumber::normalize($data['phone']);
        $customer = $phone !== '' ? User::findByPhone($phone) : null;
        // Editing other fields must not demote the original when a duplicate exists.
        $isDuplicate = $phone === PhoneNumber::normalize((string) $lead->phone)
            ? (bool) $lead->is_duplicate
            : ($phone !== '' && Lead::query()
                ->where('phone', $phone)
                ->whereKeyNot($lead->getKey())
                ->exists());

        $status = $data['status'] ?? $lead->status;

        $lead->fill([
            'name' => $data['name'],
            'phone' => $phone,
            'email' => $data['email'] ?? null,
            'source' => $data['source'] ?? $lead->source,
            'status' => $status,
            'spa_branch_id' => array_key_exists('spa_branch_id', $data) ? $data['spa_branch_id'] : $lead->spa_branch_id,
            'beautician_id' => array_key_exists('beautician_id', $data) ? $data['beautician_id'] : $lead->beautician_id,
            'customer_id' => $customer?->id,
            'is_duplicate' => $isDuplicate,
            'is_existing_customer' => $customer !== null,
        ]);

        if ($status === Lead::STATUS_FOLLOW_UP && $lead->isDirty('status')) {
            $lead->last_followed_up_at = now();
        }

        $lead->save();

        return $lead->fresh(['spaBranch', 'beautician', 'customer']) ?? $lead;
    }

    public function delete(Lead $lead): void
    {
        $lead->delete();
    }

    public function refreshMatchFlags(Lead $lead): Lead
    {
        $phone = PhoneNumber::normalize((string) $lead->phone);
        $customer = $phone !== '' ? User::findByPhone($phone) : null;
        $isDuplicate = $phone !== '' && Lead::query()
            ->where('phone', $phone)
            ->whereKeyNot($lead->getKey())
            ->exists();

        $lead->forceFill([
            'is_duplicate' => $isDuplicate,
            'is_existing_customer' => $customer !== null,
            'customer_id' => $customer?->id ?? $lead->customer_id,
        ])->saveQuietly();

        return $lead;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Lead $lead): array
    {
        $branch = $lead->spaBranch;
        $beautician = $lead->beautician;
        $lastAt = $lead->last_followed_up_at ?? $lead->updated_at ?? $lead->created_at;

        $customerLabel = 'New Lead';
        if ($lead->status === Lead::STATUS_CONVERTED) {
            $customerLabel = 'Converted';
        } elseif ($lead->is_existing_customer) {
            $customerLabel = 'Existing';
        }

        return [
            'id' => (int) $lead->id,
            'code' => $lead->code,
            'date' => $lead->created_at?->format('d M Y') ?? '',
            'name' => (string) $lead->name,
            'phone' => $lead->phone_e164 !== '' ? $lead->phone_e164 : (string) $lead->phone,
            'email' => (string) ($lead->email ?? ''),
            'source' => (string) $lead->source,
            'status' => $lead->status_label,
            'status_key' => (string) $lead->status,
            'beautician' => $beautician?->name ?? '—',
            'beautician_id' => $lead->beautician_id,
            'branch' => $branch?->name ?? '—',
            'branch_id' => $lead->spa_branch_id,
            // Payment verify slice will replace these stubs.
            'payment' => 'Not Paid',
            'sales' => 0,
            'last' => $lastAt?->format('d M g:i A') ?? '',
            'last_followed_up_at' => $lead->last_followed_up_at?->toIso8601String(),
            'days_since_followup' => $this->daysSinceFollowUp($lead),
            'days_in_pipeline' => $this->daysInPipeline($lead),
            'followup_bucket' => $this->followUpBucketFor($lead),
            'customer' => $customerLabel,
            'duplicate' => (bool) $lead->is_duplicate,
            'existing' => (bool) $lead->is_existing_customer,
            'customer_id' => $lead->customer_id,
            'created_at' => $lead->created_at?->toIso8601String(),
            'updated_at' => $lead->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<array{value:string,label:string}>
     */
    public function statusOptions(): array
    {
        return collect(Lead::statuses())
            ->map(fn (string $key) => [
                'value' => $key,
                'label' => (new Lead(['status' => $key]))->status_label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id:int,name:string}>
     */
    public function beauticianOptions(): array
    {
        return Beautician::query()
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn (Beautician $b) => [
                'id' => (int) $b->id,
                'name' => $b->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id:int,name:string,code:?string}>
     */
    public function branchOptions(): array
    {
        return SpaBranch::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (SpaBranch $b) => [
                'id' => (int) $b->id,
                'name' => (string) $b->name,
                'code' => $b->code,
            ])
            ->values()
            ->all();
    }

    private function positiveIntOrNull(mixed $raw): ?int
    {
        if ($raw === null || $raw === '' || $raw === 'all') {
            return null;
        }

        $id = (int) $raw;

        return $id > 0 ? $id : null;
    }

    /**
     * @return array{0:\Carbon\Carbon|null,1:\Carbon\Carbon|null}
     */
    private function resolveMonthRange(mixed $month): array
    {
        $key = strtolower(trim((string) $month));
        if ($key === '' || $key === 'all') {
            return [null, null];
        }

        if (! preg_match('/^(\d{4})-(\d{2})$/', $key, $m)) {
            return [null, null];
        }

        $year = (int) $m[1];
        $mon = (int) $m[2];
        if ($mon < 1 || $mon > 12) {
            return [null, null];
        }

        $from = \Carbon\Carbon::createFromDate($year, $mon, 1)->startOfMonth();

        return [$from, $from->copy()->endOfMonth()];
    }

    /**
     * @return list<array{value:string,label:string}>
     */
    public function monthOptions(int $count = 12): array
    {
        $now = now()->startOfMonth();
        $options = [];
        for ($i = 0; $i < $count; $i++) {
            $m = $now->copy()->subMonthsNoOverflow($i);
            $options[] = [
                'value' => $m->format('Y-m'),
                'label' => $i === 0
                    ? trans('lead::central.common.this_month') . ' (' . $m->format('M Y') . ')'
                    : $m->format('F Y'),
            ];
        }

        return $options;
    }

    /**
     * Active pipeline statuses that belong on the Follow-Up board.
     *
     * @return list<string>
     */
    public function followUpQueueStatuses(): array
    {
        return [
            Lead::STATUS_NEW,
            Lead::STATUS_CLAIMED,
            Lead::STATUS_FOLLOW_UP,
            Lead::STATUS_BOOKING,
            Lead::STATUS_NO_RESPONSE,
        ];
    }

    /**
     * @param  array{
     *     q?:string|null,
     *     bucket?:string|null,
     *     branch?:int|string|null,
     *     beautician?:int|string|null,
     *     per_page?:int
     * }  $filters
     */
    public function paginateFollowUps(array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 25)));
        $bucket = strtolower(trim((string) ($filters['bucket'] ?? 'all')));

        $query = Lead::query()
            ->with(['spaBranch:id,name,code', 'beautician:id,first_name,last_name', 'customer:id,first_name,last_name,phone'])
            ->search($filters['q'] ?? null);

        $branchId = $this->positiveIntOrNull($filters['branch'] ?? null);
        if ($branchId !== null) {
            $query->where('spa_branch_id', $branchId);
        }

        $beauticianId = $this->positiveIntOrNull($filters['beautician'] ?? null);
        if ($beauticianId !== null) {
            $query->where('beautician_id', $beauticianId);
        }

        $staleBefore = now()->subDays(2);

        match ($bucket) {
            'overdue' => $query
                ->whereIn('status', $this->followUpQueueStatuses())
                ->where(function ($q) use ($staleBefore): void {
                    $q->where(function ($inner) use ($staleBefore): void {
                        $inner->whereNull('last_followed_up_at')
                            ->where('created_at', '<', $staleBefore);
                    })->orWhere('last_followed_up_at', '<', $staleBefore);
                }),
            'due_today' => $query
                ->whereIn('status', $this->followUpQueueStatuses())
                ->where(function ($q): void {
                    $q->whereDate('last_followed_up_at', now()->toDateString())
                        ->orWhere(function ($inner): void {
                            $inner->whereNull('last_followed_up_at')
                                ->whereDate('created_at', now()->toDateString());
                        });
                }),
            'no_response' => $query->where('status', Lead::STATUS_NO_RESPONSE),
            'lost' => $query->where('status', Lead::STATUS_LOST),
            default => $query->whereIn('status', $this->followUpQueueStatuses()),
        };

        return $query
            ->orderByRaw('COALESCE(last_followed_up_at, created_at) ASC')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array{
     *     queue:int,
     *     overdue:int,
     *     due_today:int,
     *     no_response:int,
     *     lost:int
     * }
     */
    public function followUpSummary(?int $branchId = null): array
    {
        $base = Lead::query();
        if ($branchId !== null) {
            $base->where('spa_branch_id', $branchId);
        }

        $staleBefore = now()->subDays(2);
        $queueStatuses = $this->followUpQueueStatuses();

        $queue = (clone $base)->whereIn('status', $queueStatuses)->count();
        $overdue = (clone $base)
            ->whereIn('status', $queueStatuses)
            ->where(function ($q) use ($staleBefore): void {
                $q->where(function ($inner) use ($staleBefore): void {
                    $inner->whereNull('last_followed_up_at')
                        ->where('created_at', '<', $staleBefore);
                })->orWhere('last_followed_up_at', '<', $staleBefore);
            })
            ->count();
        $dueToday = (clone $base)
            ->whereIn('status', $queueStatuses)
            ->where(function ($q): void {
                $q->whereDate('last_followed_up_at', now()->toDateString())
                    ->orWhere(function ($inner): void {
                        $inner->whereNull('last_followed_up_at')
                            ->whereDate('created_at', now()->toDateString());
                    });
            })
            ->count();
        $noResponse = (clone $base)->where('status', Lead::STATUS_NO_RESPONSE)->count();
        $lost = (clone $base)->where('status', Lead::STATUS_LOST)->count();

        return [
            'queue' => $queue,
            'overdue' => $overdue,
            'due_today' => $dueToday,
            'no_response' => $noResponse,
            'lost' => $lost,
        ];
    }

    public function markFollowedUp(Lead $lead): Lead
    {
        $lead->last_followed_up_at = now();

        if (in_array($lead->status, [Lead::STATUS_NEW, Lead::STATUS_CLAIMED, Lead::STATUS_NO_RESPONSE], true)) {
            $lead->status = Lead::STATUS_FOLLOW_UP;
        }

        $lead->save();

        return $lead->fresh(['spaBranch', 'beautician', 'customer']) ?? $lead;
    }

    private function daysSinceFollowUp(Lead $lead): int
    {
        $anchor = $lead->last_followed_up_at ?? $lead->created_at ?? now();

        return (int) $anchor->diffInDays(now());
    }

    private function daysInPipeline(Lead $lead): int
    {
        $created = $lead->created_at ?? now();

        return (int) $created->diffInDays(now());
    }

    private function followUpBucketFor(Lead $lead): string
    {
        if ($lead->status === Lead::STATUS_LOST) {
            return 'lost';
        }
        if ($lead->status === Lead::STATUS_NO_RESPONSE) {
            return 'no_response';
        }

        $anchor = $lead->last_followed_up_at ?? $lead->created_at;
        if ($anchor && $anchor->lt(now()->subDays(2)) && in_array($lead->status, $this->followUpQueueStatuses(), true)) {
            return 'overdue';
        }

        if ($anchor && $anchor->isToday() && in_array($lead->status, $this->followUpQueueStatuses(), true)) {
            return 'due_today';
        }

        return 'queue';
    }
}
