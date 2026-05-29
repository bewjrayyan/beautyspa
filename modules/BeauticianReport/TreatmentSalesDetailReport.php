<?php

namespace Modules\BeauticianReport;

use Modules\Beautician\Entities\Beautician;
use Modules\Order\Entities\Order;
use Modules\Support\Money;
use Illuminate\Support\Facades\DB;

class TreatmentSalesDetailReport extends Report
{
    protected function view(): string
    {
        return 'beauticianreport::admin.reports.treatment_sales.index';
    }

    public function render($request)
    {
        $query = $this->report($request);

        $stats = [
            'totalOrders' => (clone $query)->count(),
            'totalSales' => Money::inDefaultCurrency((clone $query)->sum('orders.total')),
            'completedOrders' => (clone $query)->where('orders.status', Order::COMPLETED)->count(),
            'totalProducts' => (int) (clone $query)->sum(DB::raw('op.qty')),
            'withAppointment' => (clone $query)->whereNotNull('orders.appointment_date')->count(),
        ];

        $report = $query
            ->simplePaginate(20)
            ->appends($request->query());

        return view($this->view())
            ->with(array_merge(compact('report', 'stats'), $this->data()));
    }


    protected function query()
    {
        return Order::query()
            ->join('beauticians', 'orders.beautician_id', '=', 'beauticians.id')
            ->withoutCanceledOrders()
            ->select([
                'orders.id',
                'orders.status',
                'orders.customer_first_name',
                'orders.customer_last_name',
                'orders.customer_email',
                'orders.customer_phone',
                'orders.appointment_date',
                'orders.appointment_time',
                'orders.sub_total',
                'orders.discount',
                'orders.shipping_cost',
                'orders.total',
                'orders.created_at',
            ])
            ->selectRaw(Beautician::sqlFullName() . ' as beautician_name')
            ->selectRaw('beauticians.job_title as beautician_job_title')
            ->join(DB::raw('(SELECT order_id, sum(qty) qty FROM order_products GROUP BY order_id) op'), function ($join) {
                $join->on('orders.id', '=', 'op.order_id');
            })
            ->selectRaw('op.qty as total_products')
            ->orderByDesc('orders.created_at');
    }

    protected function data(): array
    {
        return [
            'beauticians' => \Modules\Beautician\Entities\Beautician::namesForFilter(),
        ];
    }
}
