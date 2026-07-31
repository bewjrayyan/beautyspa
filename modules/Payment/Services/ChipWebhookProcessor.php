<?php

namespace Modules\Payment\Services;

use Modules\Checkout\Services\CheckoutPaymentFinalizer;
use Modules\Order\Entities\Order;
use Modules\Payment\Libraries\Chip\ChipCollectClient;
use Modules\Payment\Responses\ChipWebhookTransaction;

class ChipWebhookProcessor
{
    public function process(string $purchaseId): void
    {
        $client = new ChipCollectClient(
            setting('chip_brand_id'),
            setting('chip_api_key'),
        );
        $purchase = $client->getPurchase($purchaseId);

        if (! $client->isPaid($purchase)) {
            return;
        }

        $orderId = $this->resolveOrderId($purchase);

        if ($orderId === null) {
            return;
        }

        $order = Order::query()->find($orderId);

        if (! $order || ! ChipPaymentMethodConfig::isChipPaymentMethod($order->payment_method)) {
            return;
        }

        app(CheckoutPaymentFinalizer::class)->finalize(
            $order,
            (string) $order->getRawOriginal('payment_method'),
            new ChipWebhookTransaction($purchaseId)
        );
    }

    /** @param array<string, mixed> $purchase */
    private function resolveOrderId(array $purchase): ?int
    {
        $reference = (string) ($purchase['reference'] ?? '');

        return preg_match('/^order_(\d+)$/', $reference, $matches)
            ? (int) $matches[1]
            : null;
    }
}
