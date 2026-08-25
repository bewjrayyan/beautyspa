<?php

namespace Modules\Order\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Setting\Support\WhatsAppMessageTemplate;
use Modules\Sms\Exceptions\SmsException;
use Modules\Sms\Sms;

class SendOrderStatusChangedSms implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [15, 60, 180];

    public function handle(OrderStatusChanged $event): void
    {
        if (! in_array($event->order->status, setting('sms_order_statuses', []), true)) {
            return;
        }

        if (! $event->order->customer_phone) {
            return;
        }

        try {
            Sms::send(
                $event->order->customer_phone,
                $this->message($event->order)
            );
        } catch (SmsException $e) {
            report($e);
        }
    }

    private function message(Order $order): string
    {
        return WhatsAppMessageTemplate::render('whatsapp_order_status_message', [
            'first_name' => $order->customer_first_name,
            'order_id' => (string) $order->id,
            'status' => mb_strtolower($order->status()),
            'store' => setting('store_name'),
        ], trans('sms::messages.order_status_changed', [
            'first_name' => $order->customer_first_name,
            'order_id' => $order->id,
            'status' => mb_strtolower($order->status()),
        ]));
    }
}
