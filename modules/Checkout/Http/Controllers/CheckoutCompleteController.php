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
use Modules\Checkout\Events\OrderPlaced;
use Modules\Checkout\Services\OrderGoogleCalendarUrl;
use Modules\Checkout\Services\OrderService;
use Modules\Order\Services\SendOrderBeauticianNotification;
use Modules\Checkout\Services\CheckoutCompletionGuard;
use Modules\Payment\Libraries\Bkash\BkashService;
use Modules\Payment\Libraries\Nagad\NagadPayment;
use Modules\Payment\Responses\VerifiedPaymentResponse;
use Modules\Payment\Services\GatewayPaymentVerifier;

class CheckoutCompleteController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param int $orderId
     * @param OrderService $orderService
     *
     * @return RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(int $orderId, OrderService $orderService)
    {
        if (request()->query('paymentMethod') === 'iyzico') {
            try {
                $request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
                $request->setLocale(
                    locale() === 'tr'
                        ? \Iyzipay\Model\Locale::TR
                        : \Iyzipay\Model\Locale::EN
                );
                $request->setConversationId(request()->query('reference'));
                $request->setToken($_POST['token']);

                $options = new \Iyzipay\Options();
                $options->setApiKey(setting('iyzico_api_key'));
                $options->setSecretKey(setting('iyzico_api_secret'));
                $options->setBaseUrl(
                    setting('iyzico_test_mode')
                        ? 'https://sandbox-api.iyzipay.com'
                        : 'https://api.iyzipay.com'
                );

                $response = \Iyzipay\Model\CheckoutForm::retrieve($request, $options);

                if ($response->getPaymentStatus() !== 'SUCCESS') {
                    return redirect()->route('checkout.payment_canceled.store', ['orderId' => $orderId, 'paymentMethod' => request()->query('paymentMethod')]);
                }

                $order = Order::findOrFail($orderId);
                CheckoutCompletionGuard::assertCanComplete($order, 'iyzico');

                $order->storeTransaction(
                    new VerifiedPaymentResponse($order, (string) request()->query('reference'))
                );

                event(new OrderPlaced($order));

                return redirect()->route('checkout.complete.show');
            } catch (Exception $e) {
                return redirect()->route('checkout.payment_canceled.store', ['orderId' => $orderId, 'paymentMethod' => request()->query('paymentMethod')]);
            }
        }

        if (request()->query('paymentMethod') === 'bkash') {
            $redirectToCancel = redirect()->route('checkout.payment_canceled.store', [
                'orderId' => $orderId,
                'paymentMethod' => 'bkash'
            ]);

            try {
                $status = strtolower(request('status'));
                $paymentId = request('paymentID');

                if ($status !== 'success' || !$paymentId) {
                    return $redirectToCancel;
                }

                $bkashConfig = [
                    'sandbox' => (bool) setting('bkash_test_mode'),
                    'app_key' => setting('bkash_app_key'),
                    'app_secret' => setting('bkash_app_secret'),
                    'username' => setting('bkash_username'),
                    'password' => setting('bkash_password'),
                    'timezone' => 'Asia/Dhaka'
                ];

                $bkash = new BkashService($bkashConfig);
                $paymentResponse = $bkash->executePayment($paymentId);

                if (
                    !$paymentResponse ||
                    strtolower($paymentResponse['transactionStatus'] ?? '') !== 'completed'
                ) {
                    return $redirectToCancel;
                }

                $order = Order::findOrFail($orderId);
                CheckoutCompletionGuard::assertCanComplete($order, 'bkash');
                $order->storeTransaction(
                    new VerifiedPaymentResponse($order, (string) $paymentId)
                );

                event(new OrderPlaced($order));

                return redirect()->route('checkout.complete.show');
            } catch (\Exception $e) {
                Log::error('Bkash callback error: ' . $e->getMessage());

                return $redirectToCancel;
            }
        }

        if (request()->query('paymentMethod') === 'nagad') {
            $redirectToCancel = redirect()->route('checkout.payment_canceled.store', [
                'orderId' => $orderId,
                'paymentMethod' => 'nagad',
            ]);

            try {
                if (! request()->has('payment_ref_id')) {
                    return $redirectToCancel;
                }

                $order = Order::findOrFail($orderId);
                CheckoutCompletionGuard::assertCanComplete($order, 'nagad');

                app(GatewayPaymentVerifier::class)->verifyNagad(request('payment_ref_id'));

                $order->storeTransaction(
                    new VerifiedPaymentResponse($order, (string) request('payment_ref_id'))
                );

                event(new OrderPlaced($order));

                return redirect()->route('checkout.complete.show');
            } catch (Exception $e) {
                Log::warning('Nagad callback failed', [
                    'order_id' => $orderId,
                    'message' => $e->getMessage(),
                ]);

                return $redirectToCancel;
            }
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

        $gateway = Gateway::get($paymentMethod);

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

        $order->storeTransaction($response);

        event(new OrderPlaced($order));

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

        return view('order::admin.orders.print.show', compact('order'));
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
