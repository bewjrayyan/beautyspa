<?php

namespace Modules\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Modules\Payment\Jobs\ProcessChipWebhookPurchase;
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

        ProcessChipWebhookPurchase::dispatch($purchaseId)->afterResponse();

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

}
