<?php

namespace Modules\Order\Listeners;

use Exception;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Services\CompletedOrderGroupWhatsAppMessage;
use Modules\User\Services\OneSenderWhatsAppService;

class SendCompletedOrderGroupWhatsApp
{
    public function __construct(
        private readonly CompletedOrderGroupWhatsAppMessage $messageBuilder,
        private readonly OneSenderWhatsAppService $oneSender,
    ) {
    }


    public function handle(OrderStatusChanged $event): void
    {
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
                    'dedupe_key' => 'order:' . $event->order->id . ':group',
                ]
            );
        } catch (Exception $exception) {
            report($exception);
        }
    }
}
