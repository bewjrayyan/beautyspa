<?php

namespace Modules\Checkout\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Modules\Cart\Facades\Cart;
use Modules\Order\Entities\Order;
use Modules\Checkout\Services\CheckoutCompletionGuard;

class PaymentCanceledController
{
    /**
     * Cancel a pending checkout order after the customer abandons online payment.
     *
     * @param int $orderId
     */
    public function store(Request $request, $orderId)
    {
        $order = Order::query()->findOrFail($orderId);

        try {
            CheckoutCompletionGuard::assertCanCancelPayment($order);
        } catch (Exception $exception) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 403);
            }

            return redirect()
                ->route('checkout.create')
                ->with('error', $exception->getMessage());
        }

        session()->forget('checkout_pending_order');

        $order->forceDelete();

        Cart::restoreStock();

        if ($request->ajax()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => trans('payment::messages.payment_cancelled'),
                ]
            );
        }

        return redirect()
            ->route('checkout.create')
            ->with('error', trans('payment::messages.payment_cancelled'));
    }
}
