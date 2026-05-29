<?php

namespace Modules\User\Services;

use Illuminate\Support\Facades\DB;
use Modules\Setting\Support\SettingValues;
use Modules\User\Entities\OneSenderMessageLog;
use Modules\User\Entities\OneSenderOutboundMessage;
use Modules\User\Jobs\ProcessOneSenderOutboundMessage;

class OneSenderOutboundQueueService
{
    public function isEnabled(): bool
    {
        return SettingValues::isTruthy('onesender_outbound_queue_enabled', true);
    }


    public function delaySeconds(): int
    {
        return max(0, min(3600, (int) setting('onesender_outbound_delay_seconds', 30)));
    }


    public function isDuplicateInQueue(string $recipient, string $fingerprint, array $context = []): bool
    {
        $dedupeKey = $this->normalizeDedupeKey($context['dedupe_key'] ?? null);

        if ($dedupeKey !== null) {
            return OneSenderOutboundMessage::query()
                ->where('dedupe_key', $dedupeKey)
                ->whereIn('status', [
                    OneSenderOutboundMessage::STATUS_PENDING,
                    OneSenderOutboundMessage::STATUS_PROCESSING,
                ])
                ->exists();
        }

        $hash = hash('sha256', $fingerprint);

        return OneSenderOutboundMessage::query()
            ->where('recipient', $recipient)
            ->where('message_hash', $hash)
            ->where('status', OneSenderOutboundMessage::STATUS_PENDING)
            ->exists();
    }


    /**
     * @param  array{source?: string, dedupe_key?: string}  $context
     */
    public function enqueue(
        string $recipient,
        string $recipientType,
        string $messageType,
        string $fingerprint,
        array $payload,
        array $context = [],
    ): OneSenderOutboundMessage {
        $scheduledAt = now()->addSeconds($this->delaySeconds());

        $message = OneSenderOutboundMessage::query()->create([
            'recipient' => $recipient,
            'recipient_type' => $recipientType,
            'message_type' => $messageType,
            'message_hash' => hash('sha256', $fingerprint),
            'dedupe_key' => $this->normalizeDedupeKey($context['dedupe_key'] ?? null),
            'source' => $context['source'] ?? null,
            'message_preview' => $this->preview($fingerprint),
            'payload' => $payload,
            'status' => OneSenderOutboundMessage::STATUS_PENDING,
            'scheduled_at' => $scheduledAt,
        ]);

        ProcessOneSenderOutboundMessage::dispatch($message->id)
            ->delay($scheduledAt);

        return $message;
    }


    public function process(int $messageId): void
    {
        $message = $this->claimForProcessing($messageId);

        if (! $message) {
            return;
        }

        app(OneSenderWhatsAppService::class)->deliverQueuedPayload($message);
    }


    public function processDueBatch(int $limit = 50): int
    {
        $processed = 0;

        $ids = OneSenderOutboundMessage::query()
            ->where('status', OneSenderOutboundMessage::STATUS_PENDING)
            ->where('scheduled_at', '<=', now())
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id');

        foreach ($ids as $id) {
            $this->process((int) $id);
            $processed++;
        }

        return $processed;
    }


    public function cancel(OneSenderOutboundMessage $message): bool
    {
        if (! $message->canBeCancelled()) {
            return false;
        }

        $message->update([
            'status' => OneSenderOutboundMessage::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        app(OneSenderMessageLogger::class)->recordSkipped(
            $message->recipient,
            $message->recipient_type,
            $message->message_type,
            (string) $message->message_preview,
            OneSenderMessageLog::STATUS_SKIPPED_CANCELLED,
            [
                'source' => $message->source,
                'dedupe_key' => $message->dedupe_key,
            ]
        );

        return true;
    }


    public function cancelAllPending(): int
    {
        $count = 0;

        OneSenderOutboundMessage::query()
            ->where('status', OneSenderOutboundMessage::STATUS_PENDING)
            ->orderBy('id')
            ->chunkById(100, function ($messages) use (&$count) {
                foreach ($messages as $message) {
                    if ($this->cancel($message)) {
                        $count++;
                    }
                }
            });

        return $count;
    }


    public function deleteMessage(OneSenderOutboundMessage $message): bool
    {
        if (! $message->canBeDeleted()) {
            return false;
        }

        return (bool) $message->delete();
    }


    public function markSent(OneSenderOutboundMessage $message): void
    {
        $message->update([
            'status' => OneSenderOutboundMessage::STATUS_SENT,
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }


    public function markFailed(OneSenderOutboundMessage $message, string $errorMessage): void
    {
        $message->update([
            'status' => OneSenderOutboundMessage::STATUS_FAILED,
            'error_message' => mb_substr($errorMessage, 0, 2000),
        ]);
    }


    public function pendingCount(): int
    {
        return OneSenderOutboundMessage::query()
            ->where('status', OneSenderOutboundMessage::STATUS_PENDING)
            ->count();
    }


    private function claimForProcessing(int $messageId): ?OneSenderOutboundMessage
    {
        return DB::transaction(function () use ($messageId) {
            /** @var OneSenderOutboundMessage|null $message */
            $message = OneSenderOutboundMessage::query()
                ->whereKey($messageId)
                ->lockForUpdate()
                ->first();

            if (! $message || $message->status !== OneSenderOutboundMessage::STATUS_PENDING) {
                return null;
            }

            if ($message->scheduled_at->isFuture()) {
                return null;
            }

            $message->update([
                'status' => OneSenderOutboundMessage::STATUS_PROCESSING,
                'processing_at' => now(),
            ]);

            return $message->fresh();
        });
    }


    private function normalizeDedupeKey(mixed $dedupeKey): ?string
    {
        if (! is_string($dedupeKey)) {
            return null;
        }

        $dedupeKey = trim($dedupeKey);

        return $dedupeKey === '' ? null : mb_substr($dedupeKey, 0, 191);
    }


    private function preview(string $fingerprint): string
    {
        $preview = trim($fingerprint);

        if (mb_strlen($preview) <= 500) {
            return $preview;
        }

        return mb_substr($preview, 0, 497) . '…';
    }
}
