<?php

namespace Modules\Report;

use Modules\Order\Entities\Order;
use Modules\Report\Concerns\FiltersBySpaBranch;
use Modules\Report\Concerns\JoinsOrderReportDetails;

class CustomersOrderReport extends Report
{
    use FiltersBySpaBranch;
    use JoinsOrderReportDetails;

    protected $filters = ['from', 'to', 'status', 'group', 'spa_branch_id'];

    protected function data()
    {
        return [
            'spaBranches' => $this->spaBranchesForFilter(),
        ];
    }

    protected function view()
    {
        return 'report::admin.reports.customers_order_report.index';
    }


    protected function query()
    {
        $query = Order::withTrashed()
            ->select('orders.customer_id');

        $this->applyOrderReportDetailJoins($query);
        $this->addOrderReportDetailSelects($query);

        return $query
            ->selectRaw('(SELECT COALESCE(SUM(qty), 0) FROM order_products WHERE order_products.order_id = orders.id) as total_products')
            ->addSelect('orders.total')
            ->when(request()->has('customer_name'), function ($query) {
                $query->where(function ($nameQuery) {
                    $nameQuery->where('customer_first_name', 'like', request('customer_name') . '%')
                        ->orWhere('customer_last_name', 'like', request('customer_name') . '%');
                });
            })
            ->when(request()->has('customer_email'), function ($query) {
                $query->where('customer_email', request('customer_email'));
            })
            ->orderByDesc('orders.created_at');
    }
}
