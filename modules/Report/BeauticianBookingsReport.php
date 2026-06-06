<?php

namespace Modules\Report;

use Modules\Beautician\Entities\Beautician;
use Modules\Order\Entities\Order;

class BeauticianBookingsReport extends BeauticianAwareReport
{
    protected $date = 'orders.appointment_date';

    protected function view(): string
    {
        return 'report::admin.reports.beautician_bookings.index';
    }

    protected function query()
    {
        return Order::withTrashed()
            ->join('beauticians', 'orders.beautician_id', '=', 'beauticians.id')
            ->when(is_module_enabled('SpaBranch'), function ($query) {
                $query->leftJoin('spa_branches', 'orders.spa_branch_id', '=', 'spa_branches.id');
            })
            ->whereNotNull('orders.beautician_id')
            ->whereNotNull('orders.appointment_date')
            ->withoutCanceledOrders()
            ->select([
                'orders.id',
                'orders.status',
                'orders.payment_status',
                'orders.customer_first_name',
                'orders.customer_last_name',
                'orders.customer_email',
                'orders.customer_phone',
                'orders.appointment_date',
                'orders.appointment_time',
                'orders.total',
                'orders.created_at',
            ])
            ->selectRaw(Beautician::sqlFullName() . ' as beautician_name')
            ->selectRaw('beauticians.job_title as beautician_job_title')
            ->selectRaw('beauticians.phone as beautician_phone')
            ->when(is_module_enabled('SpaBranch'), function ($query) {
                $query->selectRaw('spa_branches.name as spa_branch_name');
            })
            ->with(['products' => function ($query) {
                $query->with('product');
            }])
            ->orderByDesc('orders.appointment_date')
            ->orderBy('orders.appointment_time');
    }

    protected function data(): array
    {
        return [
            'beauticians' => $this->beauticiansForFilter(),
            'spaBranches' => $this->spaBranchesForFilter(),
        ];
    }
}
