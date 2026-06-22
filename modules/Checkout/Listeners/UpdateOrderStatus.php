<?php

namespace Modules\Checkout\Listeners;

use Modules\Order\Entities\Order;
use Modules\Checkout\Events\OrderPlaced;
use Modules\Order\Events\OrderStatusChanged;

class UpdateOrderStatus
{
    /**
     * Handle the event.
     *
     * @param OrderPlaced $event
     *
     * @return void
     */
    public function handle($event)
    {
        $order = $event->order->loadMissing('transaction');

        // WooCommerce: payment_complete → order completed (virtual/treatment);
        // payment state tracked separately in payment_status.
        if ($order->transaction?->transaction_id) {
            $previousStatus = $order->status;

            $order->update([
                'status' => Order::COMPLETED,
                'payment_status' => Order::PAYMENT_PAID,
            ]);

            if ($previousStatus !== Order::COMPLETED) {
                event(new OrderStatusChanged($order));
            }

            return;
        }

        $order->update([
            'status' => Order::PENDING,
            'payment_status' => Order::PAYMENT_PENDING,
        ]);
    }
}
