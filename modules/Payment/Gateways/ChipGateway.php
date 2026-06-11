<?php

namespace Modules\Payment\Gateways;

use Exception;
use Illuminate\Http\Request;
use Modules\Order\Entities\Order;
use Modules\Payment\GatewayInterface;
use Modules\Payment\Libraries\Chip\ChipCollectClient;
use Modules\Payment\Responses\ChipResponse;
use Modules\Payment\Services\ChipPaymentMethodConfig;
use Modules\Payment\Services\ChipPaymentMethodsResolver;
use Modules\Payment\Services\ChipPurchaseProductsBuilder;

class ChipGateway implements GatewayInterface
{
    public const SUPPORTED_CURRENCIES = ['MYR', 'SGD', 'USD'];

    public $label;

    public $description;


    public function __construct(
        private readonly string $gatewayKey,
    ) {
        $this->label = $this->resolveLabel();
        $this->description = $this->resolveDescription();
    }


    /**
     * @throws Exception
     */
    public function purchase(Order $order, Request $request): ChipResponse
    {
        if (! in_array(currency(), self::SUPPORTED_CURRENCIES, true)) {
            throw new Exception(trans('payment::messages.chip_currency_not_supported'));
        }

        $client = $this->client();
        $resolver = app(ChipPaymentMethodsResolver::class);

        $order = $order->fresh();
        $order->loadMissing([
            'products.product',
            'products.variations',
            'products.options.values',
            'taxes',
        ]);

        if ($order->products->isEmpty()) {
            throw new Exception(trans('payment::messages.chip_order_products_missing'));
        }

        $surcharge = $resolver->surchargeSubunit($this->gatewayKey, $order);

        $products = (new ChipPurchaseProductsBuilder())->build($order, $surcharge);

        if ($products === []) {
            throw new Exception(trans('payment::messages.chip_zero_amount'));
        }

        $purchaseTotal = $this->purchaseTotalSubunit($order, $products);

        $payload = [
            'brand_id' => setting('chip_brand_id'),
            'reference' => 'order_' . $order->id,
            'client' => [
                'email' => $order->customer_email,
                'full_name' => $order->customer_full_name,
                'phone' => $order->customer_phone,
            ],
            'purchase' => [
                'currency' => currency(),
                'products' => $products,
            ],
            'success_redirect' => $this->successRedirectUrl($order),
            'failure_redirect' => $this->failureRedirectUrl($order),
            'cancel_redirect' => $this->failureRedirectUrl($order),
        ];

        $payload['purchase']['total_override'] = $purchaseTotal;

        $whitelist = $resolver->resolveWhitelist($this->gatewayKey);

        if ($whitelist !== []) {
            $payload['payment_method_whitelist'] = $whitelist;
        }

        if ($callbackUrl = $this->successCallbackUrl()) {
            $payload['success_callback'] = $callbackUrl;
        }

        $purchase = $client->createPurchase($payload);

        if (empty($purchase['checkout_url'])) {
            throw new Exception(trans('payment::messages.chip_checkout_failed'));
        }

        $order->storeTransaction(new ChipResponse($order, $purchase));

        return new ChipResponse($order, $purchase);
    }


    public function complete(Order $order): ChipResponse
    {
        $order->loadMissing('transaction');

        $purchaseId = request('id')
            ?? request('purchase_id')
            ?? $order->transaction?->transaction_id;

        if (! $purchaseId) {
            throw new Exception(trans('payment::messages.chip_invalid_callback'));
        }

        $purchase = $this->client()->getPurchase($purchaseId);

        if (! $this->client()->isPaid($purchase)) {
            throw new Exception(trans('payment::messages.chip_payment_not_completed'));
        }

        $reference = (string) ($purchase['reference'] ?? '');

        if ($reference !== '' && $reference !== 'order_' . $order->id) {
            throw new Exception(trans('payment::messages.chip_invalid_callback'));
        }

        return new ChipResponse($order, $purchase);
    }


    /**
     * @param  array<int, array{name: string, price: int, quantity: int, discount?: int}>  $products
     */
    private function purchaseTotalSubunit(Order $order, array $products): int
    {
        $total = 0;

        foreach ($products as $line) {
            $lineTotal = $line['price'] * $line['quantity'];
            $lineTotal -= (int) ($line['discount'] ?? 0);
            $total += $lineTotal;
        }

        return $total;
    }


    private function client(): ChipCollectClient
    {
        return new ChipCollectClient(
            setting('chip_brand_id'),
            setting('chip_api_key'),
        );
    }


    private function successRedirectUrl(Order $order, ?string $purchaseId = null): string
    {
        $params = [
            'orderId' => $order->id,
            'paymentMethod' => $this->gatewayKey,
        ];

        if ($purchaseId) {
            $params['id'] = $purchaseId;
        }

        return route('checkout.complete.store', $params);
    }


    private function failureRedirectUrl(Order $order): string
    {
        return route('checkout.payment_canceled.store', [
            'orderId' => $order->id,
            'paymentMethod' => $this->gatewayKey,
        ]);
    }


    private function successCallbackUrl(): ?string
    {
        $url = trim((string) setting('chip_webhook_url'));

        if ($url === '' || $this->isLocalhostUrl($url)) {
            return null;
        }

        return $url;
    }


    private function isLocalhostUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return true;
        }

        $host = strtolower($host);

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test');
    }


    private function resolveLabel(): string
    {
        if ($this->gatewayKey === ChipPaymentMethodConfig::METHOD_ALL) {
            return (string) setting('chip_label');
        }

        $config = ChipPaymentMethodConfig::configFor($this->gatewayKey);

        return (string) setting($config['label_setting'] ?? 'chip_label');
    }


    private function resolveDescription(): string
    {
        if ($this->gatewayKey === ChipPaymentMethodConfig::METHOD_ALL) {
            return (string) setting('chip_description');
        }

        $config = ChipPaymentMethodConfig::configFor($this->gatewayKey);

        return (string) setting($config['description_setting'] ?? 'chip_description');
    }
}
