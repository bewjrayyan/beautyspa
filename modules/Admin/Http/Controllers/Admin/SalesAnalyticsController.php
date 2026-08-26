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
        $payload = Cache::remember('admin.dashboard.sales_analytics.v3-booked', now()->addMinutes(5), function () {
            $hasBranches = is_module_enabled('SpaBranch');

            $labels = [];
            $months = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->translatedFormat('M Y');
                $months[] = $date;
            }

            if ($hasBranches) {
                $branches = \Modules\SpaBranch\Entities\SpaBranch::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray();

                $branchDatasets = [];

                foreach ($branches as $branchId => $branchName) {
                    $amounts = [];
                    $formatted = [];
                    $orderCounts = [];

                    foreach ($months as $date) {
                        $start = $date->copy()->startOfMonth();
                        $end = $date->copy()->endOfMonth();

                        $total = Order::query()
                            ->withoutCanceledOrders()
                            ->where('spa_branch_id', $branchId)
                            ->whereBetween('created_at', [$start, $end])
                            ->sum('total');

                        $count = Order::query()
                            ->withoutCanceledOrders()
                            ->where('spa_branch_id', $branchId)
                            ->whereBetween('created_at', [$start, $end])
                            ->count();

                        $amounts[] = (float) $total;
                        $formatted[] = Money::inDefaultCurrency($total)->format();
                        $orderCounts[] = $count;
                    }

                    $branchDatasets[] = [
                        'branch_name' => $branchName,
                        'amounts' => $amounts,
                        'formatted' => $formatted,
                        'orders' => $orderCounts,
                    ];
                }

                $noBranchAmounts = [];
                $noBranchFormatted = [];
                $noBranchOrders = [];

                foreach ($months as $date) {
                    $start = $date->copy()->startOfMonth();
                    $end = $date->copy()->endOfMonth();

                    $total = Order::query()
                        ->withoutCanceledOrders()
                        ->whereNull('spa_branch_id')
                        ->whereBetween('created_at', [$start, $end])
                        ->sum('total');

                    $count = Order::query()
                        ->withoutCanceledOrders()
                        ->whereNull('spa_branch_id')
                        ->whereBetween('created_at', [$start, $end])
                        ->count();

                    $noBranchAmounts[] = (float) $total;
                    $noBranchFormatted[] = Money::inDefaultCurrency($total)->format();
                    $noBranchOrders[] = $count;
                }

                if (array_sum($noBranchAmounts) > 0 || array_sum($noBranchOrders) > 0) {
                    $branchDatasets[] = [
                        'branch_name' => trans('admin::dashboard.sales_analytics.no_branch'),
                        'amounts' => $noBranchAmounts,
                        'formatted' => $noBranchFormatted,
                        'orders' => $noBranchOrders,
                    ];
                }

                return [
                    'labels' => $labels,
                    'branches' => $branchDatasets,
                ];
            }

            $data = [];
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

                $data[] = [
                    'total' => [
                        'amount' => (float) $totalAmount,
                        'formatted' => Money::inDefaultCurrency($totalAmount)->format(),
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
