<?php

namespace Modules\Payment\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Modules\Checkout\Events\OrderPlaced;
use Modules\Order\Entities\Order;
use Modules\Payment\Libraries\Chip\ChipCollectClient;
use Modules\Payment\Responses\ChipWebhookTransaction;
use Modules\Payment\Services\ChipPaymentMethodConfig;

class ChipWebhookController
{
    public function handle(Request $request): Response
    {
        if (! setting('chip_enabled')) {
            return response('Ignored', 200);
        }

        $webhookSecret = trim((string) setting('chip_webhook_secret'));

        if ($webhookSecret === '') {
            Log::warning('CHIP webhook rejected: webhook secret is not configured');

            return response('Unauthorized', 401);
        }

        $signature = (string) ($request->header('X-Signature') ?? $request->header('X-Chip-Signature') ?? '');

        if ($signature === '' || ! hash_equals($webhookSecret, $signature)) {
            Log::warning('CHIP webhook rejected: invalid signature');

            return response('Unauthorized', 401);
        }

        $purchaseId = $request->input('id') ?? $request->input('purchase_id');

        if (! $purchaseId) {
            return response('Ignored', 200);
        }

        dispatch(fn () => $this->processPurchase((string) $purchaseId))->afterResponse();

        return response('OK', 200);
    }


    private function processPurchase(string $purchaseId): void
    {
        try {
            $client = new ChipCollectClient(
                setting('chip_brand_id'),
                setting('chip_api_key'),
            );

            $purchase = $client->getPurchase($purchaseId);

            if (! $client->isPaid($purchase)) {
                return;
            }

            $orderId = $this->resolveOrderId($purchase);

            if (! $orderId) {
                return;
            }

            $order = Order::find($orderId);

            if (! $order || ! ChipPaymentMethodConfig::isChipPaymentMethod($order->payment_method)) {
                return;
            }

            if (! in_array($order->status, [Order::PENDING, Order::PENDING_PAYMENT], true)) {
                return;
            }

            if ($order->payment_status === Order::PAYMENT_PAID) {
                return;
            }

            $order->storeTransaction(new ChipWebhookTransaction($purchaseId));

            event(new OrderPlaced($order));
        } catch (Exception $e) {
            Log::error('CHIP webhook processing failed', [
                'purchase_id' => $purchaseId,
                'message' => $e->getMessage(),
            ]);
        }
    }


    /**
     * @param array<string, mixed> $purchase
     */
    private function resolveOrderId(array $purchase): ?int
    {
        $reference = (string) ($purchase['reference'] ?? '');

        if (preg_match('/^order_(\d+)$/', $reference, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
