<?php

namespace Modules\Checkout\Services;

use Exception;
use Modules\Order\Entities\Order;

class CheckoutCompletionGuard
{
    private const OFFLINE_METHODS = ['cod', 'bank_transfer', 'check_payment'];

    /**
     * @throws Exception
     */
    public static function assertCanComplete(Order $order, string $paymentMethod): void
    {
        if ($order->payment_method !== $paymentMethod) {
            throw new Exception(trans('payment::messages.payment_method_mismatch'));
        }

        if ($order->isPaymentPaid()) {
            throw new Exception(trans('payment::messages.order_already_paid'));
        }

        if (! in_array($order->status, [Order::PENDING, Order::PENDING_PAYMENT], true)) {
            throw new Exception(trans('payment::messages.order_not_payable'));
        }

        if (in_array($paymentMethod, self::OFFLINE_METHODS, true)) {
            self::assertOfflineCheckoutSession($order);
        }
    }

    /**
     * @throws Exception
     */
    private static function assertOfflineCheckoutSession(Order $order): void
    {
        $pendingOrderId = (int) session('checkout_pending_order');

        if ($pendingOrderId !== (int) $order->id) {
            throw new Exception(trans('payment::messages.invalid_checkout_session'));
        }

        session()->forget('checkout_pending_order');
    }

    public static function rememberPendingOrder(Order $order): void
    {
        session(['checkout_pending_order' => $order->id]);
    }
}
