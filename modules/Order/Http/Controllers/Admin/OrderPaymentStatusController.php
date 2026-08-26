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
            abort(422, trans('order::messages.invalid_payment_status'));
        }

        $previousPayment = $order->payment_status;
        $previousOrder = $order->status;

        if ($previousPayment === $paymentStatus) {
            return trans('order::messages.payment_status_updated');
        }

        $updates = ['payment_status' => $paymentStatus];
        $changeType = 'payment';

        if ($paymentStatus === Order::PAYMENT_PAID && $order->status === Order::PENDING_PAYMENT) {
            $updates['status'] = Order::COMPLETED;
            $changeType = 'order_and_payment';
        }

        if ($paymentStatus === Order::PAYMENT_CANCELED && ! in_array($order->status, [Order::CANCELED, Order::REFUNDED], true)) {
            $updates['status'] = Order::CANCELED;
            $changeType = 'order_and_payment';
        }

        $order->update($updates);
        $order = $order->fresh();

        $newValue = $changeType === 'order_and_payment'
            ? ($order->status . '/' . $order->payment_status)
            : $order->payment_status;

        $previousValue = $changeType === 'order_and_payment'
            ? ($previousOrder . '/' . $previousPayment)
            : $previousPayment;

        event(new OrderStatusChanged($order, $changeType, $previousValue, $newValue));

        return trans('order::messages.payment_status_updated');
    }
}
