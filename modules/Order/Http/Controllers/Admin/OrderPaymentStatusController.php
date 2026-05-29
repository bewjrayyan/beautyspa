<?php

namespace Modules\Order\Http\Controllers\Admin;

use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;

class OrderPaymentStatusController
{
    public function update(Order $order): string
    {
        $paymentStatus = request('payment_status');

        if (! in_array($paymentStatus, Order::paymentStatuses(), true)) {
            abort(422, 'Invalid payment status.');
        }

        $updates = ['payment_status' => $paymentStatus];

        if ($paymentStatus === Order::PAYMENT_PAID && $order->status === Order::PENDING_PAYMENT) {
            $updates['status'] = Order::COMPLETED;
        }

        if ($paymentStatus === Order::PAYMENT_CANCELED && ! in_array($order->status, [Order::CANCELED, Order::REFUNDED], true)) {
            $updates['status'] = Order::CANCELED;
        }

        $order->update($updates);

        event(new OrderStatusChanged($order));

        return trans('order::messages.payment_status_updated');
    }
}
