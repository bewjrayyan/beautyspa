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
use Modules\Payment\Services\ChipWebhookSignatureVerifier;

class ChipWebhookController
{
    public function handle(Request $request, ChipWebhookSignatureVerifier $verifier): Response
    {
        if (! setting('chip_enabled')) {
            return response('OK', 200);
        }

        $rawBody = $request->getContent();
        $signature = (string) ($request->header('X-Signature') ?? $request->header('X-Chip-Signature') ?? '');

        if (! $verifier->verify($rawBody, $signature)) {
            Log::warning('CHIP callback ignored: invalid or missing RSA signature');

            return response('OK', 200);
        }

        $purchaseId = $this->resolvePurchaseId($request, $rawBody);

        if (! $purchaseId) {
            return response('OK', 200);
        }

        dispatch(fn () => $this->processPurchase($purchaseId))->afterResponse();

        return response('OK', 200);
    }


    private function resolvePurchaseId(Request $request, string $rawBody): ?string
    {
        $payload = json_decode($rawBody, true);

        if (is_array($payload)) {
            foreach (['id', 'purchase_id'] as $key) {
                if (! empty($payload[$key])) {
                    return (string) $payload[$key];
                }
            }

            if (! empty($payload['object']['id'])) {
                return (string) $payload['object']['id'];
            }
        }

        $fromInput = $request->input('id') ?? $request->input('purchase_id');

        return $fromInput ? (string) $fromInput : null;
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
