<?php

namespace Modules\Payment\Services;

use Modules\Payment\Libraries\Chip\ChipCollectClient;

class ChipWebhookSignatureVerifier
{
    public function verify(string $rawBody, ?string $signatureHeader): bool
    {
        $signatureHeader = trim((string) $signatureHeader);

        if ($signatureHeader === '' || $rawBody === '') {
            return false;
        }

        $publicKeyPem = $this->resolvePublicKeyPem();

        if ($publicKeyPem === null) {
            return false;
        }

        $publicKey = openssl_pkey_get_public($publicKeyPem);

        if ($publicKey === false) {
            return false;
        }

        $signature = base64_decode($signatureHeader, true);

        if ($signature === false || $signature === '') {
            return false;
        }

        return openssl_verify($rawBody, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    private function resolvePublicKeyPem(): ?string
    {
        $configured = trim((string) setting('chip_public_key'));

        if ($configured !== '') {
            return $this->normalizePem($configured);
        }

        if (! setting('chip_enabled')) {
            return null;
        }

        try {
            $client = new ChipCollectClient(
                (string) setting('chip_brand_id'),
                (string) setting('chip_api_key'),
            );

            $response = $client->getPublicKey();
            $key = trim((string) ($response['public_key'] ?? ''));

            return $key !== '' ? $this->normalizePem($key) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizePem(string $key): string
    {
        $key = trim($key);

        if (str_contains($key, 'BEGIN PUBLIC KEY')) {
            return $key;
        }

        $body = preg_replace('/\s+/', '', $key) ?? '';

        if ($body === '') {
            return $key;
        }

        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split($body, 64, "\n")
            . "-----END PUBLIC KEY-----";
    }
}
