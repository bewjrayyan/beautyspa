<?php

namespace Modules\BeauticianReport;

use Modules\Beautician\Entities\Beautician;
use Modules\Order\Entities\Order;

class AppointmentsReport extends Report
{
    protected $date = 'orders.appointment_date';

    protected function view(): string
    {
        return 'beauticianreport::admin.reports.appointments.index';
    }

    protected function query()
    {
        return Order::query()
            ->join('beauticians', 'orders.beautician_id', '=', 'beauticians.id')
            ->whereNotNull('orders.appointment_date')
            ->withoutCanceledOrders()
            ->selectRaw('orders.appointment_date')
            ->selectRaw('orders.appointment_time')
            ->selectRaw(Beautician::sqlFullName() . ' as beautician_name')
            ->selectRaw('beauticians.job_title as beautician_job_title')
            ->selectRaw('COUNT(orders.id) as total_orders')
            ->selectRaw('SUM(orders.total) as total')
            ->selectRaw('MIN(orders.created_at) as first_booked_at')
            ->groupBy(
                'orders.appointment_date',
                'orders.appointment_time',
                'beauticians.id',
                'beauticians.first_name',
                'beauticians.last_name',
                'beauticians.job_title'
            )
            ->orderByDesc('orders.appointment_date')
            ->orderBy('orders.appointment_time');
    }

    protected function data(): array
    {
        return [
            'beauticians' => $this->beauticiansForFilter(),
        ];
    }

    private function beauticiansForFilter()
    {
        return \Modules\Beautician\Entities\Beautician::namesForFilter();
    }
}
