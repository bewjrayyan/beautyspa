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
        ];
    }
}
