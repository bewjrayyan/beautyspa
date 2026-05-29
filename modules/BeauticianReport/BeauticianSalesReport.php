<?php

namespace Modules\BeauticianReport;

use Modules\Beautician\Entities\Beautician;
use Modules\Order\Entities\Order;
use Illuminate\Support\Facades\DB;

class BeauticianSalesReport extends Report
{
    protected function view(): string
    {
        return 'beauticianreport::admin.reports.beautician_sales.index';
    }

    protected function query()
    {
        return Order::query()
            ->join('beauticians', 'orders.beautician_id', '=', 'beauticians.id')
            ->whereNotNull('orders.beautician_id')
            ->withoutCanceledOrders()
            ->selectRaw('beauticians.id as beautician_id')
            ->selectRaw(Beautician::sqlFullName() . ' as beautician_name')
            ->selectRaw('beauticians.job_title as beautician_job_title')
            ->selectRaw('COUNT(orders.id) as total_orders')
            ->selectRaw('SUM(orders.sub_total) as sub_total')
            ->selectRaw('SUM(orders.discount) as discount')
            ->selectRaw('SUM(orders.shipping_cost) as shipping_cost')
            ->selectRaw('SUM(orders.total) as total')
            ->join(DB::raw('(SELECT order_id, sum(qty) qty FROM order_products GROUP BY order_id) op'), function ($join) {
                $join->on('orders.id', '=', 'op.order_id');
            })
            ->selectRaw('SUM(op.qty) as total_products')
            ->groupBy('beauticians.id', 'beauticians.first_name', 'beauticians.last_name', 'beauticians.job_title')
            ->orderByDesc('total');
    }

    protected function data(): array
    {
        return [
            'beauticians' => \Modules\Beautician\Entities\Beautician::namesForFilter(),
        ];
    }
}
