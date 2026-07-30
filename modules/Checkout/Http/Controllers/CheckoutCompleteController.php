<?php

namespace Modules\Checkout\Http\Controllers;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Modules\Order\Entities\Order;
use Modules\Payment\Facades\Gateway;
use Modules\Checkout\Services\OrderGoogleCalendarUrl;
use Modules\Checkout\Services\CheckoutPaymentFinalizer;
use Modules\Order\Services\SendOrderBeauticianNotification;
use Modules\Checkout\Services\CheckoutCompletionGuard;
use Modules\Payment\Services\PaymentGatewayResolver;

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
        } catch (Exception $e) {
            Log::warning('Checkout payment complete failed', [
                'order_id' => $orderId,
                'payment_method' => request('paymentMethod'),
                'message' => $e->getMessage(),
            ]);

            if (! request()->ajax()) {
                return redirect()
                    ->route('checkout.create')
                    ->with('error', $e->getMessage());
            }

            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        $paymentFinalizer->finalize($order, $paymentMethod, $response);

        if (! request()->ajax()) {
            return redirect()->route('checkout.complete.show');
        }

        return response()->json([
            'redirectUrl' => storefront_route('checkout.complete.show'),
        ]);
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

        session()->reflash('placed_order');

        $googleCalendarUrl = $calendarUrl->forOrder($order);
        $hasTreatmentBooking = $this->hasTreatmentBooking($order);
        $canNotifyBeautician = $hasTreatmentBooking
            && $order->beautician_id
            && setting('whatsapp_completed_beautician_enabled', true);

        $orderRewards = null;

        if (app('modules')->isEnabled('Loyalty')) {
            $orderRewards = app(\Modules\Loyalty\Services\LoyaltyOrderCompleteRewardsService::class)
                ->forOrder($order);
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

        session()->reflash('placed_order');

        $order->load(['products', 'coupon', 'taxes', 'beautician']);

        return view('order::admin.orders.print.show', [
            'order' => $order,
            'autoPrint' => false,
        ]);
    }


    public function notifyBeautician(SendOrderBeauticianNotification $notification)
    {
        $order = $this->resolvePlacedOrder();

        if (! $order) {
            return redirect()->route('home');
        }

        try {
            $notification->send($order);

            return redirect()
                ->route('checkout.complete.show')
                ->with('success', trans('storefront::order_complete.beautician_notify_sent'));
        } catch (Exception $e) {
            return redirect()
                ->route('checkout.complete.show')
                ->with('error', $e->getMessage());
        }
    }


    private function resolvePlacedOrder(): ?Order
    {
        $placed = session('placed_order');

        if (! $placed) {
            return null;
        }

        $orderId = $placed instanceof Order ? $placed->id : (int) $placed;

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
            ])
            ->find($orderId);
    }


    private function hasTreatmentBooking(Order $order): bool
    {
        if ($order->beautician_id || $order->appointment_date) {
            return true;
        }

        return $order->products->contains(fn ($line) => (bool) $line->product?->is_virtual);
    }
}
