<?php

namespace Modules\Order\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Services\OrderStatusWhatsAppNotifier;

class SendOrderStatusChangedSms implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [15, 60, 180];

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
