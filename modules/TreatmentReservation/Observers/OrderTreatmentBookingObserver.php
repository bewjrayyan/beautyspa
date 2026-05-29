<?php

namespace Modules\TreatmentReservation\Observers;

use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Services\BookingSyncService;

class OrderTreatmentBookingObserver
{
    public function __construct(private BookingSyncService $sync) {}


    public function created(Order $order): void
    {
        $this->sync->syncFromOrder($order);
    }


    public function updated(Order $order): void
    {
        if ($order->wasChanged([
            'beautician_id',
            'appointment_date',
            'appointment_time',
            'status',
            'payment_status',
            'customer_first_name',
            'customer_last_name',
            'customer_phone',
            'customer_email',
            'total',
            'note',
        ])) {
            $this->sync->syncFromOrder($order);
        }
    }


    public function deleted(Order $order): void
    {
        $this->sync->trashBookingsForOrder($order);
    }


    public function restored(Order $order): void
    {
        $this->sync->restoreBookingsForOrder($order);
        $this->sync->syncFromOrder($order);
    }


    public function forceDeleted(Order $order): void
    {
        $this->sync->forceDeleteBookingsForOrder($order);
    }
}
