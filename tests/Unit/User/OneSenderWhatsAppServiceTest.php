<?php

namespace Tests\Unit\User;

use Illuminate\Support\Facades\Http;
use Modules\User\Entities\OneSenderOutboundMessage;
use Modules\User\Services\OneSenderMessageLogger;
use Modules\User\Services\OneSenderOutboundQueueService;
use Modules\User\Services\OneSenderWhatsAppService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OneSenderWhatsAppServiceTest extends TestCase
{
    #[Test]
    public function immediate_delivery_queues_a_fallback_when_the_provider_fails(): void
    {
        Http::fake(['*' => Http::response(['success' => false], 503)]);

        $logger = new class extends OneSenderMessageLogger {
            public function isDuplicate(string $recipient, string $fingerprint, array $context = []): bool
            {
                return false;
            }

            public function isSendingPaused(): bool
            {
                return false;
            }

            public function recordFailed(
                string $recipient,
                string $recipientType,
                string $messageType,
                string $fingerprint,
                ?\Illuminate\Http\Client\Response $response,
                string $errorMessage,
                array $context = [],
            ): void {
            }
        };
        $queue = new class extends OneSenderOutboundQueueService {
            public array $enqueued = [];

            public function isEnabled(): bool
            {
                return true;
            }

            public function isDuplicateInQueue(string $recipient, string $fingerprint, array $context = []): bool
            {
                return false;
            }

            public function enqueue(
                string $recipient,
                string $recipientType,
                string $messageType,
                string $fingerprint,
                array $payload,
                array $context = [],
            ): OneSenderOutboundMessage {
                $this->enqueued[] = compact(
                    'recipient',
                    'recipientType',
                    'messageType',
                    'fingerprint',
                    'payload',
                    'context',
                );

                return new OneSenderOutboundMessage();
            }
        };

        $this->app->instance(OneSenderMessageLogger::class, $logger);
        $this->app->instance(OneSenderOutboundQueueService::class, $queue);

        $service = new class extends OneSenderWhatsAppService {
            public static function isConfigured(): bool
            {
                return true;
            }

            public static function allowsRealOutbound(): bool
            {
                return true;
            }
        };

        $accepted = $service->sendToGroup('120363012345678901@g.us', 'Completed', [
            'source' => 'test.completed.group',
            'dedupe_key' => 'order:1:group',
            'immediate' => true,
            'fallback_to_queue' => true,
        ]);

        $this->assertTrue($accepted);
        $this->assertCount(1, $queue->enqueued);
        $this->assertSame('group', $queue->enqueued[0]['recipientType']);
        $this->assertSame('order:1:group', $queue->enqueued[0]['context']['dedupe_key']);
    }

    #[Test]
    public function group_recipient_must_match_the_exact_whatsapp_group_format(): void
    {
        $this->expectException(\Exception::class);

        (new OneSenderWhatsAppService())->sendToGroup(
            '120363012345678901@g.us.invalid',
            'Completed',
        );
    }

    #[Test]
    public function paused_queued_delivery_is_deferred_without_consuming_a_retry(): void
    {
        $logger = new class extends OneSenderMessageLogger {
            public function isSendingPaused(): bool
            {
                return true;
            }
        };
        $queue = new class extends OneSenderOutboundQueueService {
            public ?OneSenderOutboundMessage $deferred = null;

            public function defer(
                OneSenderOutboundMessage $message,
                string $reason,
                int $delaySeconds = 60,
            ): void {
                $this->deferred = $message;
            }
        };
        $message = new class extends OneSenderOutboundMessage {
            public function refresh()
            {
                return $this;
            }
        };
        $message->setRawAttributes([
            'status' => OneSenderOutboundMessage::STATUS_PROCESSING,
            'recipient' => '120363012345678901@g.us',
            'recipient_type' => 'group',
            'message_type' => 'text',
            'message_preview' => 'Completed',
            'payload' => json_encode([['type' => 'text']]),
            'attempts' => 0,
        ], true);

        $this->app->instance(OneSenderMessageLogger::class, $logger);
        $this->app->instance(OneSenderOutboundQueueService::class, $queue);

        $service = new class extends OneSenderWhatsAppService {
            public static function isConfigured(): bool
            {
                return true;
            }
        };
        $service->deliverQueuedPayload($message);

        $this->assertSame($message, $queue->deferred);
        $this->assertSame(0, $message->attempts);
    }
}
