<?php

declare(strict_types=1);

namespace Modules\Lead\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Modules\Loyalty\Entities\LoyaltyStampWallet;
use Modules\Loyalty\Entities\LoyaltyTier;
use Modules\Loyalty\Entities\LoyaltyTransaction;
use Modules\Loyalty\Entities\LoyaltyWallet;

final class CentralWalletService
{
    /**
     * @param  array{
     *     q?:string|null,
     *     segment?:string|null,
     *     tier?:int|string|null,
     *     customer_id?:int|string|null,
     *     per_page?:int
     * }  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 25)));

        return $this->baseQuery($filters)
            ->with(['user', 'tier:id,name', 'transactions' => static fn ($q) => $q->limit(5)])
            ->withCount('transactions')
            ->orderByDesc('balance')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     members:int,
     *     with_balance:int,
     *     zero_balance:int,
     *     points_outstanding:int,
     *     stamp_ready:int
     * }
     */
    public function summary(array $filters = []): array
    {
        $base = $this->baseQuery(array_replace($filters, ['segment' => 'all']));
        $members = (clone $base)->count();
        $withBalance = (clone $base)->where('balance', '>', 0)->count();
        $points = (int) (clone $base)->sum('balance');

        return [
            'members' => $members,
            'with_balance' => $withBalance,
            'zero_balance' => max(0, $members - $withBalance),
            'points_outstanding' => $points,
            'stamp_ready' => $this->baseQuery(array_replace($filters, ['segment' => 'stamp_ready']))->count(),
        ];
    }

    /**
     * @return list<array{value:string,label:string}>
     */
    public function segmentOptions(): array
    {
        return [
            ['value' => 'all', 'label' => trans('lead::central.wallet.tab_all')],
            ['value' => 'active', 'label' => trans('lead::central.wallet.tab_active')],
            ['value' => 'zero', 'label' => trans('lead::central.wallet.tab_zero')],
            ['value' => 'stamp_ready', 'label' => trans('lead::central.wallet.tab_stamp')],
        ];
    }

    /**
     * @return list<array{id:int,name:string}>
     */
    public function tierOptions(): array
    {
        return LoyaltyTier::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static fn (LoyaltyTier $tier) => [
                'id' => (int) $tier->id,
                'name' => (string) $tier->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(LoyaltyWallet $wallet): array
    {
        $user = $wallet->user;
        $name = trim((string) ($user?->full_name ?: ''));
        if ($name === '') {
            $name = (string) ($user?->email ?: trans('lead::central.wallet.guest'));
        }

        $stampReady = 0;
        $activeStamps = 0;
        if ($user && Schema::hasTable('loyalty_stamp_wallets')) {
            $stampReady = LoyaltyStampWallet::query()
                ->where('user_id', $user->id)
                ->whereNotNull('redeemed_at')
                ->whereNull('fulfilled_at')
                ->count();
            $activeStamps = LoyaltyStampWallet::query()
                ->where('user_id', $user->id)
                ->whereNull('completed_at')
                ->whereNull('redeemed_at')
                ->where(function (Builder $q): void {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->count();
        }

        $recent = ($wallet->relationLoaded('transactions')
            ? $wallet->transactions
            : $wallet->transactions()->limit(5)->get())
            ->map(static fn (LoyaltyTransaction $tx) => [
                'id' => (int) $tx->id,
                'type' => (string) $tx->type,
                'points' => (int) $tx->points,
                'balance_after' => (int) $tx->balance_after,
                'description' => (string) ($tx->description ?: $tx->type),
                'created_at' => optional($tx->created_at)?->toDateTimeString(),
                'created_label' => optional($tx->created_at)?->format('d M Y H:i') ?: '—',
            ])
            ->values()
            ->all();

        return [
            'id' => (int) $wallet->id,
            'customer_id' => (int) ($wallet->user_id ?: 0),
            'code' => 'MEM-'.$wallet->id,
            'name' => $name,
            'phone' => (string) ($user?->phone ?: ''),
            'email' => (string) ($user?->email ?: ''),
            'initial' => mb_strtoupper(mb_substr($name !== '' ? $name : 'W', 0, 1)),
            'avatar_url' => $user?->avatarUrl(),
            'balance' => (int) $wallet->balance,
            'lifetime_spend' => round((float) $wallet->lifetime_spend, 2),
            'tier' => $wallet->tier?->name ?: null,
            'tier_id' => $wallet->tier_id ? (int) $wallet->tier_id : null,
            'member_since' => optional($wallet->created_at)?->toDateString(),
            'member_since_label' => optional($wallet->created_at)?->format('d M Y') ?: '—',
            'tier_since_label' => optional($wallet->tier_assigned_at)?->format('d M Y') ?: '—',
            'activity_count' => (int) ($wallet->transactions_count ?? $wallet->transactions?->count() ?? 0),
            'last_activity_label' => (string) (($recent[0]['created_label'] ?? null) ?: '—'),
            'stamp_ready' => $stampReady,
            'stamp_active' => $activeStamps,
            'segment' => ((int) $wallet->balance) > 0 ? 'active' : 'zero',
            'segment_label' => ((int) $wallet->balance) > 0
                ? trans('lead::central.wallet.segment_active')
                : trans('lead::central.wallet.segment_zero'),
            'recent' => $recent,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function baseQuery(array $filters): Builder
    {
        $query = $this->customerWallets();

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->whereHas('user', function (Builder $user) use ($q, $like): void {
                $user->where(function (Builder $inner) use ($q, $like): void {
                    if (ctype_digit($q)) {
                        $inner->orWhere('id', (int) $q);
                    }
                    $inner->orWhere('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            });
        }

        $customerId = $this->positiveIntOrNull($filters['customer_id'] ?? null);
        if ($customerId !== null) {
            $query->where('user_id', $customerId);
        }

        $tierId = $this->positiveIntOrNull($filters['tier'] ?? null);
        if ($tierId !== null) {
            $query->where('tier_id', $tierId);
        }

        $segment = (string) ($filters['segment'] ?? 'all');
        if ($segment === 'active') {
            $query->where('balance', '>', 0);
        } elseif ($segment === 'zero') {
            $query->where('balance', '<=', 0);
        } elseif ($segment === 'stamp_ready') {
            if (! Schema::hasTable('loyalty_stamp_wallets')) {
                return $query->whereRaw('0 = 1');
            }
            $query->whereIn('user_id', LoyaltyStampWallet::query()
                ->select('user_id')
                ->whereNotNull('redeemed_at')
                ->whereNull('fulfilled_at'));
        }

        return $query;
    }

    private function customerWallets(): Builder
    {
        $roleId = (int) setting('customer_role');

        return LoyaltyWallet::query()->whereHas('user', function (Builder $user) use ($roleId): void {
            if ($roleId > 0) {
                $user->whereHas('roles', static fn (Builder $r) => $r->where('roles.id', $roleId));
            } else {
                $user->whereRaw('0 = 1');
            }
        });
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
