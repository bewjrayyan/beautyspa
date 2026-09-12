<?php

namespace Modules\Order\Listeners;

use Exception;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Services\CompletedOrderGroupWhatsAppMessage;
use Modules\User\Services\OneSenderWhatsAppService;

class SendCompletedOrderGroupWhatsApp implements ShouldHandleEventsAfterCommit
{
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
            $sent = $this->oneSender->sendToGroup(
                $groupId,
                $this->messageBuilder->build($event->order),
                [
                    'source' => 'order.completed.group',
                    'dedupe_key' => 'order:'.$event->order->id.':group',
                    'immediate' => true,
                ]
            );

            if (! $sent) {
                report(new \RuntimeException(trans('order::whatsapp.send_failed')));
            }
        } catch (Exception $exception) {
            report($exception);
        }
    }
}
