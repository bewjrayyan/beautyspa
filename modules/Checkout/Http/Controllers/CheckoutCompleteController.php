<?php

namespace Modules\Checkout\Http\Controllers;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Modules\Order\Entities\Order;
use Modules\Checkout\Services\OrderGoogleCalendarUrl;
use Modules\Checkout\Services\CheckoutPaymentFinalizer;
use Modules\Order\Services\SendOrderBeauticianNotification;
use Modules\Checkout\Services\CheckoutCompletionGuard;
use Modules\Payment\Services\PaymentGatewayResolver;

/**
 * Checkout thank-you + payment return handler.
 * User: payment/order saved but completed page not shown (session/auth lookalike).
 */
class CheckoutCompleteController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param int $orderId
     * @param CheckoutPaymentFinalizer $paymentFinalizer
     *
     * @return RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store($orderId, CheckoutPaymentFinalizer $paymentFinalizer)
    {
        $orderId = filter_var($orderId, FILTER_VALIDATE_INT);

        if ($orderId === false || $orderId < 1) {
            if (request()->ajax()) {
                return response()->json([
                    'message' => trans('order::orders.not_found', ['id' => request()->route('orderId')]),
                ], 404);
            }

            return redirect()
                ->route('checkout.create')
                ->with('error', trans('order::orders.not_found', ['id' => request()->route('orderId')]));
        }

        $order = Order::findOrFail($orderId);
        $paymentMethod = (string) request('paymentMethod');

        try {
            CheckoutCompletionGuard::assertCanComplete($order, $paymentMethod);
        } catch (Exception $e) {
            // Chip webhook may finalize before the browser return URL hits this action.
            if (CheckoutCompletionGuard::isAlreadyPaidReturn($order, $paymentMethod)) {
                return $this->thankYouResponse($order);
            }

            Log::warning('Checkout completion guard failed', [
                'order_id' => $orderId,
                'payment_method' => $paymentMethod,
                'message' => $e->getMessage(),
            ]);

            if (! request()->ajax()) {
                return redirect()
                    ->route('checkout.create')
                    ->with('error', $e->getMessage());
            }

            return response()->json(['message' => $e->getMessage()], 403);
        }

        $gateway = PaymentGatewayResolver::get($paymentMethod);

        if ($gateway === null) {
            Log::warning('Checkout payment gateway not found', [
                'order_id' => $orderId,
                'payment_method' => $paymentMethod,
            ]);

            if (! request()->ajax()) {
                return redirect()
                    ->route('checkout.create')
                    ->with('error', trans('payment::messages.payment_gateway_error'));
            }

            return response()->json(['message' => trans('payment::messages.payment_gateway_error')], 403);
        }

        try {
            $response = $gateway->complete($order);
            $paymentFinalizer->finalize($order, $paymentMethod, $response);
        } catch (\Throwable $e) {
            Log::warning('Checkout payment complete failed', [
                'order_id' => $orderId,
                'payment_method' => request('paymentMethod'),
                'message' => $e->getMessage(),
            ]);

            $fresh = $order->fresh();
            if (
                $fresh
                && (
                    CheckoutCompletionGuard::isAlreadyPaidReturn($fresh, $paymentMethod)
                    || $this->hasTreatmentBooking($fresh->loadMissing(['products.product', 'treatmentBookings']))
                )
            ) {
                return $this->thankYouResponse($fresh);
            }

            if (! request()->ajax()) {
                return redirect()
                    ->route('checkout.create')
                    ->with('error', $e->getMessage());
            }

            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return $this->thankYouResponse($order->fresh() ?? $order);
    }


    /**
     * Display the specified resource.
     *
     * @return Application|Factory|object|View|RedirectResponse
     */
    public function show(OrderGoogleCalendarUrl $calendarUrl)
    {
        $order = $this->resolvePlacedOrder();

        if (! $order) {
            return redirect()->route('home');
        }

        CheckoutCompletionGuard::rememberPlacedOrder($order);

        $googleCalendarUrl = null;
        $orderRewards = null;

        try {
            $googleCalendarUrl = $calendarUrl->forOrder($order);
        } catch (\Throwable $e) {
            report($e);
        }

        $hasTreatmentBooking = $this->hasTreatmentBooking($order);
        $canNotifyBeautician = $hasTreatmentBooking
            && $order->beautician_id
            && setting('whatsapp_completed_beautician_enabled', true);

        if (app('modules')->isEnabled('Loyalty')) {
            try {
                $orderRewards = app(\Modules\Loyalty\Services\LoyaltyOrderCompleteRewardsService::class)
                    ->forOrder($order);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return view('storefront::public.checkout.complete.show', compact(
            'order',
            'googleCalendarUrl',
            'hasTreatmentBooking',
            'canNotifyBeautician',
            'orderRewards',
        ));
    }


    public function invoice()
    {
        $order = $this->resolvePlacedOrder();

        if (! $order) {
            return redirect()->route('home');
        }

        CheckoutCompletionGuard::keepPlacedOrder();

        $order->load(['products', 'coupon', 'taxes', 'beautician']);

        return view('order::admin.orders.print.show', [
            'order' => $order,
            'autoPrint' => request()->boolean('print'),
        ]);
    }


    public function notifyBeautician(SendOrderBeauticianNotification $notification)
    {
        $order = $this->resolvePlacedOrder();

        if (! $order) {
            return redirect()->route('home');
        }

        CheckoutCompletionGuard::keepPlacedOrder();

        try {
            $notification->send($order);

            return redirect()
                ->to(CheckoutCompletionGuard::thankYouUrl($order))
                ->with('success', trans('storefront::order_complete.beautician_notify_sent'));
        } catch (Exception $e) {
            return redirect()
                ->to(CheckoutCompletionGuard::thankYouUrl($order))
                ->with('error', $e->getMessage());
        }
    }


    private function thankYouResponse(Order $order): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $redirectUrl = CheckoutCompletionGuard::thankYouUrl($order);

        if (! request()->ajax()) {
            return redirect()->to($redirectUrl);
        }

        return response()->json([
            'orderId' => (int) $order->id,
            'redirectUrl' => $redirectUrl,
        ]);
    }


    private function resolvePlacedOrder(): ?Order
    {
        $orderId = null;
        $request = request();

        // Signed thank-you links (relative) survive /v2 + lost session cookies.
        if ($request->filled('placed_order') && $request->hasValidSignature(absolute: false)) {
            $orderId = (int) $request->query('placed_order');
        }

        if (! $orderId) {
            $orderId = CheckoutCompletionGuard::placedOrderId();
        }

        if (! $orderId) {
            return null;
        }

        return Order::query()
            ->with([
                'products.product',
                'products.variations',
                'products.options.option',
                'products.options.values',
                'coupon',
                'taxes',
                'transaction',
                'beautician',
                'spaBranch',
                'treatmentBookings.product',
                'treatmentBookings.beautician',
            ])
            ->find($orderId);
    }


    private function hasTreatmentBooking(Order $order): bool
    {
        if ($order->beautician_id || $order->appointment_date) {
            return true;
        }

        if ($order->relationLoaded('treatmentBookings') && $order->treatmentBookings->isNotEmpty()) {
            return true;
        }

        return $order->products->contains(fn ($line) => (bool) $line->product?->is_virtual);
    }
}
