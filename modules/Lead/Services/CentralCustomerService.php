<?php

declare(strict_types=1);

namespace Modules\Lead\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Lead\Entities\Lead;
use Modules\Order\Entities\Order;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\User\Entities\User;

final class CentralCustomerService
{
    /**
     * @param  array{
     *     q?:string|null,
     *     segment?:string|null,
     *     branch?:int|string|null,
     *     from?:Carbon|null,
     *     to?:Carbon|null,
     *     per_page?:int
     * }  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 25)));
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        $query = $this->baseQuery($filters)
            ->with(['loyaltyWallet.tier:id,name'])
            ->addSelect([
                'paid_orders_count' => Order::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('customer_id', 'users.id')
                    ->where('payment_status', Order::PAYMENT_PAID),
                'paid_sales' => Order::query()
                    ->selectRaw('COALESCE(SUM(total), 0)')
                    ->whereColumn('customer_id', 'users.id')
                    ->where('payment_status', Order::PAYMENT_PAID),
                'orders_count' => Order::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('customer_id', 'users.id'),
                'last_order_at' => Order::query()
                    ->selectRaw('MAX(created_at)')
                    ->whereColumn('customer_id', 'users.id'),
                'period_orders' => Order::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('customer_id', 'users.id')
                    ->where('payment_status', Order::PAYMENT_PAID)
                    ->when(
                        $from instanceof Carbon && $to instanceof Carbon,
                        fn (Builder $q) => $q->whereBetween('created_at', [
                            $from->copy()->startOfDay(),
                            $to->copy()->endOfDay(),
                        ])
                    ),
                'period_sales' => Order::query()
                    ->selectRaw('COALESCE(SUM(total), 0)')
                    ->whereColumn('customer_id', 'users.id')
                    ->where('payment_status', Order::PAYMENT_PAID)
                    ->when(
                        $from instanceof Carbon && $to instanceof Carbon,
                        fn (Builder $q) => $q->whereBetween('created_at', [
                            $from->copy()->startOfDay(),
                            $to->copy()->endOfDay(),
                        ])
                    ),
                'leads_count' => Lead::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('customer_id', 'users.id'),
            ])
            ->orderByDesc('last_order_at')
            ->orderByDesc('id');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     branch?:int|string|null,
     *     from?:Carbon|null,
     *     to?:Carbon|null
     * }  $filters
     * @return array{
     *     total:int,
     *     buyers:int,
     *     new_buyers:int,
     *     with_leads:int,
     *     period_sales:float,
     *     returning:int
     * }
     */
    public function summary(array $filters = []): array
    {
        $base = $this->customerUniverse($filters);
        $total = (clone $base)->count();

        $buyers = (clone $base)
            ->whereHas('orders', static fn (Builder $q) => $q->where('payment_status', Order::PAYMENT_PAID))
            ->count();

        $withLeads = (clone $base)
            ->whereExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('leads')
                    ->whereColumn('leads.customer_id', 'users.id');
            })
            ->count();

        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        $newBuyers = 0;
        $periodSales = 0.0;
        $returning = 0;

        if ($from instanceof Carbon && $to instanceof Carbon) {
            $range = [$from->copy()->startOfDay(), $to->copy()->endOfDay()];

            $newBuyers = (clone $base)
                ->whereHas('orders', static function (Builder $q) use ($range): void {
                    $q->where('payment_status', Order::PAYMENT_PAID)
                        ->whereBetween('created_at', $range);
                })
                ->whereDoesntHave('orders', static function (Builder $q) use ($from): void {
                    $q->where('payment_status', Order::PAYMENT_PAID)
                        ->where('created_at', '<', $from->copy()->startOfDay());
                })
                ->count();

            $returning = (clone $base)
                ->whereHas('orders', static function (Builder $q) use ($range): void {
                    $q->where('payment_status', Order::PAYMENT_PAID)
                        ->whereBetween('created_at', $range);
                })
                ->whereHas('orders', static function (Builder $q) use ($from): void {
                    $q->where('payment_status', Order::PAYMENT_PAID)
                        ->where('created_at', '<', $from->copy()->startOfDay());
                })
                ->count();

            $branchId = $this->positiveIntOrNull($filters['branch'] ?? null);
            $periodSales = (float) Order::query()
                ->where('payment_status', Order::PAYMENT_PAID)
                ->whereBetween('created_at', $range)
                ->whereNotNull('customer_id')
                ->when($branchId !== null, static fn (Builder $q) => $q->where('spa_branch_id', $branchId))
                ->sum('total');
        }

        return [
            'total' => $total,
            'buyers' => $buyers,
            'new_buyers' => $newBuyers,
            'with_leads' => $withLeads,
            'period_sales' => round($periodSales, 2),
            'returning' => $returning,
        ];
    }

    /**
     * @return list<array{value:string,label:string}>
     */
    public function segmentOptions(): array
    {
        return [
            ['value' => 'all', 'label' => trans('lead::central.customers.tab_all')],
            ['value' => 'buyers', 'label' => trans('lead::central.customers.tab_buyers')],
            ['value' => 'new', 'label' => trans('lead::central.customers.tab_new')],
            ['value' => 'returning', 'label' => trans('lead::central.customers.tab_returning')],
            ['value' => 'leads', 'label' => trans('lead::central.customers.tab_leads')],
        ];
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
    public function toArray(User $user): array
    {
        $name = trim((string) $user->full_name);
        if ($name === '') {
            $name = (string) ($user->email ?: trans('lead::central.customers.guest'));
        }

        $lastOrder = Order::query()
            ->with('spaBranch:id,name,code')
            ->where('customer_id', $user->id)
            ->latest('id')
            ->first(['id', 'spa_branch_id', 'created_at', 'payment_status', 'total']);

        $branch = $lastOrder?->spaBranch;
        $paidOrders = (int) ($user->paid_orders_count ?? 0);
        $periodOrders = (int) ($user->period_orders ?? 0);

        $segment = 'registered';
        if ($paidOrders > 0) {
            $segment = $periodOrders > 0 && $paidOrders === $periodOrders ? 'new' : 'buyer';
        }

        $tier = $user->loyaltyWallet?->tier?->name;

        return [
            'id' => (int) $user->id,
            'code' => 'CUS-'.$user->id,
            'name' => $name,
            'phone' => (string) ($user->phone ?: ''),
            'email' => (string) ($user->email ?: ''),
            'initial' => mb_strtoupper(mb_substr($name !== '' ? $name : 'C', 0, 1)),
            'avatar_url' => $user->avatarUrl(),
            'orders_count' => (int) ($user->orders_count ?? 0),
            'paid_orders_count' => $paidOrders,
            'paid_sales' => round((float) ($user->paid_sales ?? 0), 2),
            'period_orders' => $periodOrders,
            'period_sales' => round((float) ($user->period_sales ?? 0), 2),
            'leads_count' => (int) ($user->leads_count ?? 0),
            'last_order_at' => $user->last_order_at
                ? Carbon::parse((string) $user->last_order_at)->toDateTimeString()
                : null,
            'last_order_label' => $user->last_order_at
                ? Carbon::parse((string) $user->last_order_at)->format('d M Y')
                : '—',
            'branch' => $branch?->code ?: ($branch?->name ?: '—'),
            'branch_name' => $branch?->name ?: '—',
            'segment' => $segment,
            'segment_label' => match ($segment) {
                'new' => trans('lead::central.customers.segment_new'),
                'buyer' => trans('lead::central.customers.segment_buyer'),
                default => trans('lead::central.customers.segment_registered'),
            },
            'loyalty_tier' => $tier ?: null,
            'has_lead' => ((int) ($user->leads_count ?? 0)) > 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function baseQuery(array $filters): Builder
    {
        $query = $this->customerUniverse($filters);

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function (Builder $inner) use ($q, $like): void {
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q);
                }
                $inner->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        $segment = (string) ($filters['segment'] ?? 'all');
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        if ($segment === 'buyers') {
            $query->whereHas('orders', static fn (Builder $o) => $o->where('payment_status', Order::PAYMENT_PAID));
        } elseif ($segment === 'leads') {
            $query->whereExists(function ($sub): void {
                $sub->select(DB::raw(1))
                    ->from('leads')
                    ->whereColumn('leads.customer_id', 'users.id');
            });
        } elseif ($segment === 'new' && $from instanceof Carbon && $to instanceof Carbon) {
            $range = [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
            $query->whereHas('orders', static function (Builder $o) use ($range): void {
                $o->where('payment_status', Order::PAYMENT_PAID)
                    ->whereBetween('created_at', $range);
            })->whereDoesntHave('orders', static function (Builder $o) use ($from): void {
                $o->where('payment_status', Order::PAYMENT_PAID)
                    ->where('created_at', '<', $from->copy()->startOfDay());
            });
        } elseif ($segment === 'returning' && $from instanceof Carbon && $to instanceof Carbon) {
            $range = [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
            $query->whereHas('orders', static function (Builder $o) use ($range): void {
                $o->where('payment_status', Order::PAYMENT_PAID)
                    ->whereBetween('created_at', $range);
            })->whereHas('orders', static function (Builder $o) use ($from): void {
                $o->where('payment_status', Order::PAYMENT_PAID)
                    ->where('created_at', '<', $from->copy()->startOfDay());
            });
        }

        return $query;
    }

    /**
     * Registered storefront customers only (same universe as Users → Customer role).
     * Staff with incidental orders are excluded.
     *
     * @param  array<string, mixed>  $filters
     */
    private function customerUniverse(array $filters): Builder
    {
        $roleId = (int) setting('customer_role');

        $query = User::query();

        if ($roleId > 0) {
            $query->whereHas('roles', static fn (Builder $r) => $r->where('roles.id', $roleId));
        } else {
            // Misconfigured install: never treat "anyone with an order" as the customer directory.
            $query->whereRaw('0 = 1');
        }

        $branchId = $this->positiveIntOrNull($filters['branch'] ?? null);
        if ($branchId !== null) {
            $query->whereHas('orders', static fn (Builder $o) => $o->where('spa_branch_id', $branchId));
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
