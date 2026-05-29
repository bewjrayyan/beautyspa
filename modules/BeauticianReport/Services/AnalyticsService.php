<?php

namespace Modules\BeauticianReport\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Beautician\Entities\Beautician;
use Modules\Order\Entities\Order;
use Modules\Support\Money;

class AnalyticsService
{
    public function overview(): array
    {
        $treatmentQuery = Order::query()
            ->whereNotNull('beautician_id')
            ->withoutCanceledOrders();

        $today = today()->toDateString();

        return [
            'totalTreatmentSales' => Money::inDefaultCurrency((clone $treatmentQuery)->sum('total')),
            'totalTreatmentOrders' => (clone $treatmentQuery)->count(),
            'completedTreatmentOrders' => (clone $treatmentQuery)->where('status', Order::COMPLETED)->count(),
            'todayAppointments' => Order::query()
                ->whereNotNull('appointment_date')
                ->withoutCanceledOrders()
                ->whereDate('appointment_date', $today)
                ->count(),
            'upcomingAppointments' => Order::query()
                ->whereNotNull('appointment_date')
                ->withoutCanceledOrders()
                ->whereDate('appointment_date', '>=', $today)
                ->whereNotIn('status', [Order::CANCELED, Order::REFUNDED, Order::COMPLETED])
                ->count(),
            'activeBeauticians' => Beautician::where('is_active', true)->count(),
            'topBeauticians' => $this->topBeauticians(5),
            'recentTreatmentOrders' => $this->recentTreatmentOrders(8),
            'statusBreakdown' => $this->statusBreakdown(),
        ];
    }

    public function salesTrend(int $days = 30): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $labels = [];
        $amounts = [];
        $orders = [];

        $rows = Order::query()
            ->whereNotNull('beautician_id')
            ->withoutCanceledOrders()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as sale_date')
            ->selectRaw('SUM(total) as total')
            ->selectRaw('COUNT(*) as total_orders')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('sale_date');

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $row = $rows->get($date);
            $labels[] = Carbon::parse($date)->format('d M');
            $amounts[] = $this->toFloat($row->total ?? 0);
            $orders[] = (int) ($row->total_orders ?? 0);
        }

        return [
            'labels' => $labels,
            'amounts' => $amounts,
            'orders' => $orders,
            'currency' => currency_symbol(setting('default_currency')),
        ];
    }

    public function salesByBeautician(): array
    {
        $rows = Order::query()
            ->join('beauticians', 'orders.beautician_id', '=', 'beauticians.id')
            ->whereNotNull('orders.beautician_id')
            ->withoutCanceledOrders()
            ->selectRaw(Beautician::sqlFullName() . ' as label')
            ->selectRaw('SUM(orders.total) as total')
            ->groupBy('beauticians.id', 'beauticians.first_name', 'beauticians.last_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('label')->all(),
            'amounts' => $rows->pluck('total')->map(fn ($value) => $this->toFloat($value))->all(),
        ];
    }


    private function toFloat(mixed $value): float
    {
        if ($value instanceof Money) {
            return (float) $value->amount();
        }

        return (float) ($value ?? 0);
    }

    private function topBeauticians(int $limit): Collection
    {
        return Order::query()
            ->join('beauticians', 'orders.beautician_id', '=', 'beauticians.id')
            ->whereNotNull('orders.beautician_id')
            ->withoutCanceledOrders()
            ->selectRaw('beauticians.id')
            ->selectRaw(Beautician::sqlFullName() . ' as name')
            ->selectRaw('beauticians.job_title')
            ->selectRaw('COUNT(orders.id) as total_orders')
            ->selectRaw('SUM(orders.total) as total_sales')
            ->groupBy('beauticians.id', 'beauticians.first_name', 'beauticians.last_name', 'beauticians.job_title')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->total_sales = Money::inDefaultCurrency($row->total_sales);

                return $row;
            });
    }

    private function recentTreatmentOrders(int $limit): Collection
    {
        return Order::query()
            ->with('beautician:id,first_name,last_name,job_title')
            ->whereNotNull('beautician_id')
            ->latest()
            ->take($limit)
            ->get([
                'id',
                'beautician_id',
                'customer_first_name',
                'customer_last_name',
                'total',
                'status',
                'appointment_date',
                'appointment_time',
                'created_at',
            ]);
    }

    private function statusBreakdown(): array
    {
        return Order::query()
            ->whereNotNull('beautician_id')
            ->selectRaw('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status,
                'label' => trans("order::statuses.{$row->status}"),
                'count' => (int) $row->count,
            ])
            ->all();
    }
}
