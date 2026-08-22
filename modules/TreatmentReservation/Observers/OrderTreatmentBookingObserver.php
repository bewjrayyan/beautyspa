<?php

namespace Modules\TreatmentReservation\Observers;

use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Services\BookingSyncService;
use Modules\TreatmentReservation\Services\CheckoutSlotHoldService;

class OrderTreatmentBookingObserver
{
    public function __construct(private BookingSyncService $sync) {}


    public function created(Order $order): void
    {
        if (BookingSyncService::isSuppressingOrderObserverSync()) {
            return;
        }

        if (BookingSyncService::shouldDeferUntilPayment($order) && ! $order->isPaymentPaid()) {
            return;
        }

        $this->sync->syncFromOrder($order);
    }


    public function updated(Order $order): void
    {
        if (BookingSyncService::isSuppressingOrderObserverSync()) {
            return;
        }

        if (BookingSyncService::shouldDeferUntilPayment($order) && ! $order->isPaymentPaid()) {
            return;
        }

        if ($order->wasChanged([
            'beautician_id',
            'appointment_date',
            'appointment_time',
            'schedule_status',
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
        app(CheckoutSlotHoldService::class)->releaseHoldsForOrder((int) $order->id);
        $this->sync->trashBookingsForOrder($order);
    }


    public function restored(Order $order): void
    {
        $this->sync->restoreBookingsForOrder($order);
        $this->sync->syncFromOrder($order);
    }


    public function forceDeleted(Order $order): void
    {
        BookingSyncService::forgetPendingCheckoutLines((int) $order->id);
        app(CheckoutSlotHoldService::class)->releaseHoldsForOrder((int) $order->id);
        $this->sync->forceDeleteBookingsForOrder($order);
    }
}
