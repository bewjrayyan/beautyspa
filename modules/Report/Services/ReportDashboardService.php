<?php

namespace Modules\Report\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Beautician\Entities\Beautician;
use Modules\Coupon\Entities\Coupon;
use Modules\Order\Entities\Order;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\Support\Money;
use Nwidart\Modules\Facades\Module;

class ReportDashboardService
{
    public function overview(?Request $request = null): array
    {
        $request ??= request();
        $baseQuery = $this->filteredOrders($request);
        $salesSnapshot = $this->salesSnapshot($baseQuery);
        $orderCounts = (clone $baseQuery)
            ->selectRaw('SUM(CASE WHEN orders.status != ? THEN 1 ELSE 0 END) as total_orders', [Order::CANCELED])
            ->selectRaw('SUM(CASE WHEN orders.status = ? THEN 1 ELSE 0 END) as completed_orders', [Order::COMPLETED])
            ->selectRaw('SUM(CASE WHEN orders.status = ? THEN 1 ELSE 0 END) as pending_orders', [Order::PENDING])
            ->selectRaw(
                'SUM(CASE WHEN orders.status != ? AND orders.payment_status = ? THEN 1 ELSE 0 END) as paid_orders',
                [Order::CANCELED, Order::PAYMENT_PAID]
            )
            ->first();

        $data = [
            'totalSales' => Money::inDefaultCurrency($salesSnapshot['total']),
            'netSales' => Money::inDefaultCurrency($salesSnapshot['net']),
            'salesByBranch' => collect($salesSnapshot['by_branch'])->map(static function (array $row) {
                return [
                    'branch_id' => $row['branch_id'],
                    'name' => $row['name'],
                    'total' => Money::inDefaultCurrency($row['total']),
                    'refunded' => Money::inDefaultCurrency($row['refunded']),
                    'net' => Money::inDefaultCurrency($row['net']),
                ];
            })->all(),
            'totalOrders' => (int) ($orderCounts->total_orders ?? 0),
            'completedOrders' => (int) ($orderCounts->completed_orders ?? 0),
            'pendingOrders' => (int) ($orderCounts->pending_orders ?? 0),
            'paidOrders' => (int) ($orderCounts->paid_orders ?? 0),
            'hasBeautician' => $this->hasBeauticianSupport(),
            'treatmentSales' => Money::inDefaultCurrency(0),
            'treatmentOrders' => 0,
            'todayAppointments' => 0,
            'upcomingAppointments' => 0,
            'salesTrend' => $this->salesTrend($baseQuery, 14),
            'salesByBeautician' => ['labels' => [], 'amounts' => []],
        ];

        if ($data['hasBeautician']) {
            $treatmentQuery = (clone $baseQuery)
                ->whereNotNull('orders.beautician_id')
                ->withoutCanceledOrders()
                ->paid();
            $today = today()->toDateString();
            $treatment = (clone $treatmentQuery)
                ->selectRaw('COALESCE(SUM(orders.total), 0) as treatment_sales')
                ->selectRaw('COUNT(*) as treatment_orders')
                ->first();
            $appointmentQuery = (clone $baseQuery)
                ->whereNotNull('orders.appointment_date')
                ->withoutCanceledOrders();

            $data['treatmentSales'] = Money::inDefaultCurrency($treatment->treatment_sales ?? 0);
            $data['treatmentOrders'] = (int) ($treatment->treatment_orders ?? 0);
            $data['todayAppointments'] = (clone $appointmentQuery)
                ->whereDate('orders.appointment_date', $today)
                ->count();
            $data['upcomingAppointments'] = (clone $appointmentQuery)
                ->whereDate('orders.appointment_date', '>=', $today)
                ->whereNotIn('orders.status', [Order::CANCELED, Order::COMPLETED])
                ->count();
            $data['salesByBeautician'] = $this->salesByBeautician($baseQuery, 14);
            $data['treatmentSalesTrend'] = $this->treatmentSalesTrend($baseQuery, 14);
        }

        return $data;
    }

    private function filteredOrders(Request $request): Builder
    {
        $query = Order::query();
        $reportType = $request->query('type');

        match ($reportType) {
            'coupons_report' => $query->whereNotNull('orders.coupon_id'),
            'products_purchase_report' => $query->whereHas('products'),
            'shipping_report' => $query->whereNotNull('orders.shipping_method'),
            'tax_report' => $query->whereHas('taxes'),
            'beautician_bookings_report' => $query
                ->whereNotNull('orders.beautician_id')
                ->whereNotNull('orders.appointment_date'),
            default => null,
        };

        $dateColumn = $request->query('type') === 'beautician_bookings_report'
            ? 'orders.appointment_date'
            : 'orders.created_at';

        if ($request->filled('from')) {
            $query->where($dateColumn, '>=', Carbon::parse($request->query('from'))->startOfDay());
        }

        if ($request->filled('to')) {
            $query->where($dateColumn, '<', Carbon::parse($request->query('to'))->startOfDay()->addDay());
        }

        if ($request->filled('status')) {
            $query->where('orders.status', $request->query('status'));
        }

        if ($request->filled('spa_branch_id')) {
            $query->where('orders.spa_branch_id', $request->integer('spa_branch_id'));
        }

        if ($request->filled('beautician_id')) {
            $query->where('orders.beautician_id', $request->integer('beautician_id'));
        }

        if ($request->filled('coupon_code')) {
            $couponIds = Coupon::withoutGlobalScope('active')
                ->where('code', $request->query('coupon_code'))
                ->select('coupons.id');
            $query->whereIn('orders.coupon_id', $couponIds);
        }

        if ($request->filled('customer_name')) {
            $name = $request->query('customer_name');
            $query->where(function (Builder $nameQuery) use ($name) {
                $nameQuery->where('orders.customer_first_name', 'like', $name . '%')
                    ->orWhere('orders.customer_last_name', 'like', $name . '%');
            });
        }

        if ($request->filled('customer_email')) {
            $query->where('orders.customer_email', $request->query('customer_email'));
        }

        if ($request->filled('shipping_method')) {
            $query->where('orders.shipping_method', $request->query('shipping_method'));
        }

        if ($request->filled('product_id') || $request->filled('sku') || $request->filled('category_id')) {
            $query->whereHas('products', function (Builder $lineQuery) use ($request) {
                if ($request->filled('product_id')) {
                    $lineQuery->where('product_id', $request->integer('product_id'));
                }

                if ($request->filled('sku')) {
                    $lineQuery->whereHas('product', function (Builder $productQuery) use ($request) {
                        $productQuery->where('sku', $request->query('sku'));
                    });
                }

                if ($request->filled('category_id')) {
                    $lineQuery->whereHas('product.categories', function (Builder $categoryQuery) use ($request) {
                        $categoryQuery->where('categories.id', $request->integer('category_id'));
                    });
                }
            });
        }

        return $query;
    }

    private function salesSnapshot(Builder $baseQuery): array
    {
        $rows = (clone $baseQuery)
            ->select('orders.spa_branch_id')
            ->selectRaw('SUM(CASE WHEN orders.status != ? THEN orders.total ELSE 0 END) as booked_total', [Order::CANCELED])
            ->selectRaw(
                'SUM(CASE WHEN orders.status != ? AND orders.payment_status = ? THEN orders.total ELSE 0 END) as net_total',
                [Order::CANCELED, Order::PAYMENT_PAID]
            )
            ->selectRaw('SUM(CASE WHEN orders.payment_status = ? THEN orders.total ELSE 0 END) as refunded_total', [Order::PAYMENT_REFUNDED])
            ->groupBy('orders.spa_branch_id')
            ->get();

        $branchIds = $rows->pluck('spa_branch_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
        $branchNames = $branchIds->isEmpty() || ! Module::isEnabled('SpaBranch')
            ? collect()
            : SpaBranch::query()->whereIn('id', $branchIds)->pluck('name', 'id');
        $byBranch = $rows->map(function ($row) use ($branchNames) {
            $branchId = $row->spa_branch_id === null ? null : (int) $row->spa_branch_id;

            return [
                'branch_id' => $branchId,
                'name' => $branchId === null
                    ? trans('admin::dashboard.sales_analytics.no_branch')
                    : (string) ($branchNames[$branchId] ?? ('#' . $branchId)),
                'total' => (float) ($row->booked_total ?? 0),
                'refunded' => (float) ($row->refunded_total ?? 0),
                'net' => (float) ($row->net_total ?? 0),
            ];
        })->sortByDesc('total')->values();

        return [
            'total' => (float) $byBranch->sum('total'),
            'refunded' => (float) $byBranch->sum('refunded'),
            'net' => (float) $byBranch->sum('net'),
            'by_branch' => $byBranch->all(),
        ];
    }

    public function bookingPageStats(?Request $request = null): array
    {
        if (!$this->hasBeauticianSupport()) {
            return [
                'today' => 0,
                'upcoming' => 0,
                'completed' => 0,
                'totalBookings' => 0,
                'totalSales' => Money::inDefaultCurrency(0),
            ];
        }

        $today = today()->toDateString();

        $request ??= request();

        $bookingQuery = $this->filteredOrders($request)
            ->whereNotNull('beautician_id')
            ->whereNotNull('appointment_date')
            ->withoutCanceledOrders();

        $row = (clone $bookingQuery)
            ->selectRaw('COUNT(*) as total_bookings')
            ->selectRaw(
                'SUM(CASE WHEN DATE(appointment_date) = ? THEN 1 ELSE 0 END) as today_count',
                [$today]
            )
            ->selectRaw(
                'SUM(CASE WHEN appointment_date >= ? AND status NOT IN (?, ?) THEN 1 ELSE 0 END) as upcoming_count',
                [$today, Order::CANCELED, Order::COMPLETED]
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_count',
                [Order::COMPLETED]
            )
            ->first();

        $totalSales = (clone $bookingQuery)
            ->paid()
            ->sum('total');

        return [
            'today' => (int) ($row->today_count ?? 0),
            'upcoming' => (int) ($row->upcoming_count ?? 0),
            'completed' => (int) ($row->completed_count ?? 0),
            'totalBookings' => (int) ($row->total_bookings ?? 0),
            'totalSales' => Money::inDefaultCurrency($totalSales),
        ];
    }

    public function beauticianBookings(int $limit = 12, ?Request $request = null): Collection
    {
        if (!$this->hasBeauticianSupport()) {
            return collect();
        }

        $today = today()->toDateString();

        $request ??= request();

        return $this->filteredOrders($request)
            ->with(['beautician:id,first_name,last_name,job_title,phone', 'spaBranch:id,name'])
            ->whereNotNull('beautician_id')
            ->whereNotNull('appointment_date')
            ->withoutCanceledOrders()
            ->whereDate('appointment_date', '>=', today()->subDays(14))
            ->orderByRaw('CASE WHEN DATE(appointment_date) >= ? THEN 0 ELSE 1 END', [$today])
            ->orderByRaw('CASE WHEN DATE(appointment_date) >= ? THEN appointment_date END ASC', [$today])
            ->orderByRaw('CASE WHEN DATE(appointment_date) < ? THEN appointment_date END DESC', [$today])
            ->orderBy('appointment_time')
            ->take($limit)
            ->get([
                'id',
                'beautician_id',
                'customer_first_name',
                'customer_last_name',
                'customer_email',
                'customer_phone',
                'appointment_date',
                'appointment_time',
                'status',
                'payment_status',
                'total',
                'spa_branch_id',
                'created_at',
            ]);
    }

    private function hasBeauticianSupport(): bool
    {
        return Module::isEnabled('Beautician')
            && Schema::hasColumn('orders', 'beautician_id');
    }

    private function salesTrend(Builder $baseQuery, int $days): array
    {
        return $this->buildTrend(
            (clone $baseQuery)->withoutCanceledOrders(),
            'orders.created_at',
            $days
        );
    }

    private function treatmentSalesTrend(Builder $baseQuery, int $days): array
    {
        return $this->buildTrend(
            (clone $baseQuery)->whereNotNull('orders.beautician_id')->withoutCanceledOrders()->paid(),
            'orders.created_at',
            $days
        );
    }

    private function buildTrend($query, string $column, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $labels = [];
        $amounts = [];

        $rows = (clone $query)
            ->where($column, '>=', $start)
            ->selectRaw("DATE({$column}) as sale_date")
            ->selectRaw('SUM(total) as total')
            ->groupBy(DB::raw("DATE({$column})"))
            ->get()
            ->keyBy('sale_date');

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('d M');
            $amounts[] = $this->toChartAmount($rows->get($date)?->total);
        }

        return [
            'labels' => $labels,
            'amounts' => $amounts,
            'currency' => currency_symbol(setting('default_currency')),
        ];
    }

    private function salesByBeautician(Builder $baseQuery, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = (clone $baseQuery)
            ->join('beauticians', 'orders.beautician_id', '=', 'beauticians.id')
            ->whereNotNull('orders.beautician_id')
            ->where('orders.created_at', '>=', $start)
            ->withoutCanceledOrders()
            ->paid()
            ->selectRaw(Beautician::sqlFullName() . ' as label')
            ->selectRaw('SUM(orders.total) as total')
            ->groupBy('beauticians.id', 'beauticians.first_name', 'beauticians.last_name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'labels' => $rows->pluck('label')->all(),
            'amounts' => $rows->pluck('total')->map(fn ($v) => $this->toChartAmount($v))->all(),
        ];
    }

    private function toChartAmount(mixed $value): float
    {
        if ($value instanceof Money) {
            return (float) $value->amount();
        }

        return (float) ($value ?? 0);
    }
}
