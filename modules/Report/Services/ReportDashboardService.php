<?php

namespace Modules\Report\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Beautician\Entities\Beautician;
use Modules\Order\Entities\Order;
use Modules\Support\Money;
use Nwidart\Modules\Facades\Module;

class ReportDashboardService
{
    public function overview(): array
    {
        $orderQuery = Order::query()->withoutCanceledOrders();

        $data = [
            'totalSales' => Order::totalSales(),
            'totalOrders' => (clone $orderQuery)->count(),
            'completedOrders' => (clone $orderQuery)->where('status', Order::COMPLETED)->count(),
            'pendingOrders' => (clone $orderQuery)->whereIn('payment_status', [
                Order::PAYMENT_PENDING,
                Order::PAYMENT_PROCESSING,
            ])->count(),
            'paidOrders' => (clone $orderQuery)->where('payment_status', Order::PAYMENT_PAID)->count(),
            'hasBeautician' => $this->hasBeauticianSupport(),
            'treatmentSales' => Money::inDefaultCurrency(0),
            'treatmentOrders' => 0,
            'todayAppointments' => 0,
            'upcomingAppointments' => 0,
            'salesTrend' => $this->salesTrend(14),
            'salesByBeautician' => ['labels' => [], 'amounts' => []],
        ];

        if ($data['hasBeautician']) {
            $treatmentQuery = Order::query()
                ->whereNotNull('beautician_id')
                ->withoutCanceledOrders();

            $today = today()->toDateString();

            $data['treatmentSales'] = Money::inDefaultCurrency((clone $treatmentQuery)->sum('total'));
            $data['treatmentOrders'] = (clone $treatmentQuery)->count();
            $data['todayAppointments'] = Order::query()
                ->whereNotNull('appointment_date')
                ->withoutCanceledOrders()
                ->whereDate('appointment_date', $today)
                ->count();
            $data['upcomingAppointments'] = Order::query()
                ->whereNotNull('appointment_date')
                ->withoutCanceledOrders()
                ->whereDate('appointment_date', '>=', $today)
                ->whereNotIn('status', [Order::CANCELED, Order::REFUNDED, Order::COMPLETED])
                ->count();
            $data['salesByBeautician'] = $this->salesByBeautician();
            $data['treatmentSalesTrend'] = $this->treatmentSalesTrend(14);
        }

        return $data;
    }

    public function bookingPageStats(): array
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

        $row = Order::query()
            ->whereNotNull('beautician_id')
            ->whereNotNull('appointment_date')
            ->withoutCanceledOrders()
            ->selectRaw('COUNT(*) as total_bookings')
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales')
            ->selectRaw(
                'SUM(CASE WHEN DATE(appointment_date) = ? THEN 1 ELSE 0 END) as today_count',
                [$today]
            )
            ->selectRaw(
                'SUM(CASE WHEN appointment_date >= ? AND status NOT IN (?, ?, ?) THEN 1 ELSE 0 END) as upcoming_count',
                [$today, Order::CANCELED, Order::REFUNDED, Order::COMPLETED]
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_count',
                [Order::COMPLETED]
            )
            ->first();

        return [
            'today' => (int) ($row->today_count ?? 0),
            'upcoming' => (int) ($row->upcoming_count ?? 0),
            'completed' => (int) ($row->completed_count ?? 0),
            'totalBookings' => (int) ($row->total_bookings ?? 0),
            'totalSales' => Money::inDefaultCurrency($row->total_sales ?? 0),
        ];
    }

    public function beauticianBookings(int $limit = 12): Collection
    {
        if (!$this->hasBeauticianSupport()) {
            return collect();
        }

        return Order::query()
            ->with('beautician:id,first_name,last_name,job_title,phone')
            ->whereNotNull('beautician_id')
            ->whereNotNull('appointment_date')
            ->withoutCanceledOrders()
            ->whereDate('appointment_date', '>=', today())
            ->whereNotIn('status', [Order::CANCELED, Order::REFUNDED, Order::COMPLETED])
            ->orderBy('appointment_date')
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
                'total',
                'created_at',
            ]);
    }

    private function hasBeauticianSupport(): bool
    {
        return Module::isEnabled('Beautician')
            && Schema::hasColumn('orders', 'beautician_id');
    }

    private function salesTrend(int $days): array
    {
        return $this->buildTrend(
            Order::query()->withoutCanceledOrders(),
            'created_at',
            $days
        );
    }

    private function treatmentSalesTrend(int $days): array
    {
        return $this->buildTrend(
            Order::query()->whereNotNull('beautician_id')->withoutCanceledOrders(),
            'created_at',
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

    private function salesByBeautician(): array
    {
        $rows = Order::query()
            ->join('beauticians', 'orders.beautician_id', '=', 'beauticians.id')
            ->whereNotNull('orders.beautician_id')
            ->withoutCanceledOrders()
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
