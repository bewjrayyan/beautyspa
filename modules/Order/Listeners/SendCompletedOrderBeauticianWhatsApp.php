<?php

namespace Modules\Order\Listeners;

use Exception;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Services\SendOrderBeauticianNotification;
class SendCompletedOrderBeauticianWhatsApp
{
    public function __construct(
        private readonly SendOrderBeauticianNotification $notification,
    ) {
    }


    public function handle(OrderStatusChanged $event): void
    {
        if ($event->order->status !== Order::COMPLETED) {
            return;
        }

        if (! setting('whatsapp_completed_beautician_enabled', true)) {
            return;
        }

        try {
            $this->notification->send($event->order);
        } catch (Exception $exception) {
            report($exception);
        }
    }
}
