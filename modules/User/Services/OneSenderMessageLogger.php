<?php

namespace Modules\User\Services;

use Illuminate\Http\Client\Response;
use Modules\Setting\Support\SettingValues;
use Modules\User\Entities\OneSenderMessageLog;

class OneSenderMessageLogger
{
    public function isSendingPaused(): bool
    {
        return SettingValues::isTruthy('onesender_sending_paused');
    }


    public function isDuplicate(string $recipient, string $fingerprint, array $context = []): bool
    {
        if (! SettingValues::isTruthy('onesender_dedupe_enabled', true)) {
            return false;
        }

        $dedupeKey = $this->normalizeDedupeKey($context['dedupe_key'] ?? null);

        if ($dedupeKey !== null) {
            return OneSenderMessageLog::query()
                ->where('dedupe_key', $dedupeKey)
                ->whereIn('status', [
                    OneSenderMessageLog::STATUS_SENT,
                    OneSenderMessageLog::STATUS_SKIPPED_DUPLICATE,
                ])
                ->exists();
        }

        $minutes = max(1, (int) setting('onesender_dedupe_minutes', 1440));
        $hash = $this->hashFingerprint($fingerprint);

        return OneSenderMessageLog::query()
            ->where('recipient', $recipient)
            ->where('message_hash', $hash)
            ->where('status', OneSenderMessageLog::STATUS_SENT)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }


    public function recordSkipped(
        string $recipient,
        string $recipientType,
        string $messageType,
        string $fingerprint,
        string $status,
        array $context = [],
    ): void {
        $this->insertLog([
            'recipient' => $recipient,
            'recipient_type' => $recipientType,
            'message_type' => $messageType,
            'message_hash' => $this->hashFingerprint($fingerprint),
            'dedupe_key' => $this->normalizeDedupeKey($context['dedupe_key'] ?? null),
            'source' => $context['source'] ?? null,
            'message_preview' => $this->preview($fingerprint),
            'status' => $status,
        ]);
    }


    public function recordSent(
        string $recipient,
        string $recipientType,
        string $messageType,
        string $fingerprint,
        Response $response,
        array $context = [],
    ): void {
        $this->insertLog([
            'recipient' => $recipient,
            'recipient_type' => $recipientType,
            'message_type' => $messageType,
            'message_hash' => $this->hashFingerprint($fingerprint),
            'dedupe_key' => $this->normalizeDedupeKey($context['dedupe_key'] ?? null),
            'source' => $context['source'] ?? null,
            'message_preview' => $this->preview($fingerprint),
            'status' => OneSenderMessageLog::STATUS_SENT,
            'http_status' => $response->status(),
            'api_response' => $this->responseBodyForLog($response),
        ]);
    }


    public function recordFailed(
        string $recipient,
        string $recipientType,
        string $messageType,
        string $fingerprint,
        ?Response $response,
        string $errorMessage,
        array $context = [],
    ): void {
        $this->insertLog([
            'recipient' => $recipient,
            'recipient_type' => $recipientType,
            'message_type' => $messageType,
            'message_hash' => $this->hashFingerprint($fingerprint),
            'dedupe_key' => $this->normalizeDedupeKey($context['dedupe_key'] ?? null),
            'source' => $context['source'] ?? null,
            'message_preview' => $this->preview($fingerprint),
            'status' => OneSenderMessageLog::STATUS_FAILED,
            'http_status' => $response?->status(),
            'api_response' => $response ? $this->responseBodyForLog($response) : null,
            'error_message' => mb_substr($errorMessage, 0, 2000),
        ]);
    }


    private function insertLog(array $attributes): void
    {
        try {
            OneSenderMessageLog::query()->create(array_merge($attributes, [
                'created_at' => now(),
            ]));
        } catch (\Throwable $exception) {
            \Illuminate\Support\Facades\Log::error('OneSender message log insert failed', [
                'error' => $exception->getMessage(),
                'status' => $attributes['status'] ?? null,
                'recipient' => $attributes['recipient'] ?? null,
                'source' => $attributes['source'] ?? null,
            ]);
        }
    }


    private function hashFingerprint(string $fingerprint): string
    {
        return hash('sha256', $fingerprint);
    }


    private function preview(string $fingerprint): string
    {
        $preview = trim($fingerprint);

        if (mb_strlen($preview) <= 500) {
            return $preview;
        }

        return mb_substr($preview, 0, 497) . '…';
    }


    private function normalizeDedupeKey(mixed $dedupeKey): ?string
    {
        if (! is_string($dedupeKey)) {
            return null;
        }

        $dedupeKey = trim($dedupeKey);

        return $dedupeKey === '' ? null : mb_substr($dedupeKey, 0, 191);
    }


    /**
     * @return array<string, mixed>|null
     */
    private function responseBodyForLog(Response $response): ?array
    {
        $body = $response->json();

        if (is_array($body)) {
            return $body;
        }

        $raw = trim($response->body());

        return $raw === '' ? null : ['raw' => mb_substr($raw, 0, 2000)];
    }
}
