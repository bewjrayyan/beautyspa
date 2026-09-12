<?php

namespace Modules\Order\Listeners;

use Exception;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Services\SendOrderBeauticianNotification;

class SendCompletedOrderBeauticianWhatsApp implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly SendOrderBeauticianNotification $notification,
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
