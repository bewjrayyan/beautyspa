<?php

namespace Modules\Order\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Services\CompletedOrderGroupWhatsAppMessage;
use Modules\User\Services\OneSenderWhatsAppService;

class SendCompletedOrderGroupWhatsApp implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [15, 60, 180];

    public function __construct(
        private readonly CompletedOrderGroupWhatsAppMessage $messageBuilder,
        private readonly OneSenderWhatsAppService $oneSender,
    ) {
    }

    public function handle(OrderStatusChanged $event): void
    {
        $changeType = $event->changeType ?: 'order';

        // Skip payment/treatment-only events on already-completed orders.
        if (! in_array($changeType, ['order', 'order_and_payment'], true)) {
            return;
        }

        if ($event->order->status !== Order::COMPLETED) {
            return;
        }

        if (! setting('whatsapp_completed_group_enabled')) {
            return;
        }

        $groupId = trim((string) setting('onesender_whatsapp_group_id', ''));

        if ($groupId === '') {
            return;
        }

        try {
            $this->oneSender->sendToGroup(
                $groupId,
                $this->messageBuilder->build($event->order),
                [
                    'source' => 'order.completed.group',
                    'dedupe_key' => 'order:'.$event->order->id.':group',
                    'immediate' => true,
                ]
            );
        } catch (Exception $exception) {
            report($exception);

            throw $exception;
        }
    }
}
