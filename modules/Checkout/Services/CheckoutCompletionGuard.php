<?php

namespace Modules\Checkout\Services;

use Exception;
use Modules\Order\Entities\Order;

class CheckoutCompletionGuard
{
    private const OFFLINE_METHODS = ['cod', 'bank_transfer'];

    public static function isOfflineMethod(string $paymentMethod): bool
    {
        return in_array($paymentMethod, self::OFFLINE_METHODS, true);
    }


    /**
     * @return list<string>
     */
    public static function offlineMethods(): array
    {
        return self::OFFLINE_METHODS;
    }

    /**
     * @throws Exception
     */
    public static function assertCanComplete(Order $order, string $paymentMethod): void
    {
        $orderPaymentMethod = (string) $order->getRawOriginal('payment_method');

        if ($orderPaymentMethod !== $paymentMethod) {
            throw new Exception(trans('payment::messages.payment_method_mismatch'));
        }

        if ($order->isPaymentPaid()) {
            throw new Exception(trans('payment::messages.order_already_paid'));
        }

        if ($order->status !== Order::PENDING) {
            throw new Exception(trans('payment::messages.order_not_payable'));
        }

        if (self::isOfflineMethod($paymentMethod)) {
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


    /**
     * Persist thank-you page order id (not one-shot flash).
     * Callers: CheckoutCompleteController, AddPlacedOrderToSession, CheckoutController offline.
     * User: checkout completes but thank-you page not shown (auth/session lookalike).
     */
    public static function rememberPlacedOrder(Order $order): void
    {
        session([
            'placed_order' => (int) $order->id,
            'placed_order_id' => (int) $order->id,
        ]);
    }


    public static function placedOrderId(): ?int
    {
        $placed = session('placed_order_id') ?? session('placed_order');

        if ($placed instanceof Order) {
            return (int) $placed->id;
        }

        $id = (int) $placed;

        return $id > 0 ? $id : null;
    }


    public static function keepPlacedOrder(): void
    {
        session()->keep(['placed_order', 'placed_order_id']);
    }


    /**
     * Browser returned after payment already finalized (e.g. Chip webhook race).
     */
    public static function isAlreadyPaidReturn(Order $order, string $paymentMethod): bool
    {
        $orderPaymentMethod = (string) $order->getRawOriginal('payment_method');

        return $order->isPaymentPaid()
            && hash_equals($orderPaymentMethod, $paymentMethod);
    }

    /**
     * @throws Exception
     */
    public static function assertCanCancelPayment(Order $order): void
    {
        $pendingOrderId = (int) session('checkout_pending_order');

        if ($pendingOrderId !== (int) $order->id) {
            throw new Exception(trans('payment::messages.invalid_checkout_session'));
        }

        if ($order->isPaymentPaid()) {
            throw new Exception(trans('payment::messages.order_already_paid'));
        }

        if ($order->status !== Order::PENDING) {
            throw new Exception(trans('payment::messages.order_not_payable'));
        }
    }
}
