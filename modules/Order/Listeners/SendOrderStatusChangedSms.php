<?php

namespace Modules\Order\Listeners;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Services\OrderStatusWhatsAppNotifier;

class SendOrderStatusChangedSms implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly OrderStatusWhatsAppNotifier $notifier,
    ) {}

    public function handle(OrderStatusChanged $event): void
    {
        $this->notifier->notify(
            $event->order,
            $event->changeType ?: 'order',
            $event->previousValue,
            $event->newValue,
        );
    }
}
