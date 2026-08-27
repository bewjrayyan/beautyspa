<?php

namespace Modules\Checkout\Services;

use AestheticCart\Http\FixSubdirectoryRequest;
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
     * Thank-you URL that survives lost session cookies (Chip in-app browser, /v2 redirects).
     * Signs the *localized* relative path (/en/checkout/complete) so LocaleSessionRedirect
     * does not invalidate the signature. absolute:false matches FixSubdirectoryRequest.
     */
    public static function thankYouUrl(Order $order): string
    {
        self::rememberPlacedOrder($order);

        $parameters = [
            'expires' => now()->addHours(12)->getTimestamp(),
            'placed_order' => (int) $order->id,
        ];
        ksort($parameters);

        $root = rtrim((string) (FixSubdirectoryRequest::resolvedAppUrl() ?: config('app.url')), '/');
        $localizedAbsolute = storefront_route('checkout.complete.show', [], true);
        $fullPath = parse_url($localizedAbsolute, PHP_URL_PATH) ?: '/checkout/complete';
        $rootPath = rtrim((string) (parse_url($root, PHP_URL_PATH) ?: ''), '/');

        $relativePath = $fullPath;
        if ($rootPath !== '' && str_starts_with($fullPath, $rootPath)) {
            $relativePath = substr($fullPath, strlen($rootPath)) ?: '/';
        }
        if (! str_starts_with($relativePath, '/')) {
            $relativePath = '/'.$relativePath;
        }

        $query = \Illuminate\Support\Arr::query($parameters);
        $payload = $relativePath.'?'.$query;
        // UrlGenerator signs with config app.key (raw string), not encrypter->getKey().
        $signature = hash_hmac('sha256', $payload, (string) config('app.key'));

        return $root.$relativePath.'?'.$query.'&signature='.$signature;
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
