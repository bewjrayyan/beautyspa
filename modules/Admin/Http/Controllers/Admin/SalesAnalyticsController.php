<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\Order\Entities\Order;
use Modules\Support\Money;

class SalesAnalyticsController
{
    public function index(Order $order)
    {
        $payload = Cache::remember('admin.dashboard.sales_analytics', now()->addMinutes(5), function () {
            $months = [];
            $labels = [];
            $data = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->translatedFormat('M Y');
                $months[] = $date;
            }

            foreach ($months as $date) {
                $start = $date->copy()->startOfMonth();
                $end = $date->copy()->endOfMonth();

                $totalAmount = Order::query()
                    ->withoutCanceledOrders()
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('total');

                $totalOrders = Order::query()
                    ->withoutCanceledOrders()
                    ->whereBetween('created_at', [$start, $end])
                    ->count();

                $money = Money::inDefaultCurrency($totalAmount);

                $data[] = [
                    'total' => [
                        'amount' => (float) $totalAmount,
                        'formatted' => $money->format(),
                    ],
                    'total_orders' => $totalOrders,
                ];
            }

            return [
                'labels' => $labels,
                'data' => $data,
            ];
        });

        return response()->json($payload);
    }
}
