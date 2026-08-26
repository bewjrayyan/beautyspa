<?php

namespace Modules\Order\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Mail\OrderStatusChanged as OrderStatusChangedEmail;

class SendOrderStatusChangedEmail implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [15, 60, 180];

    public function handle(OrderStatusChanged $event): void
    {
        $changeType = $event->changeType ?: 'order';

        // Payment/treatment-only updates must not re-send the order-status email.
        if (! in_array($changeType, ['order', 'order_and_payment'], true)) {
            return;
        }

        if (! in_array($event->order->status, setting('email_order_statuses', []), true)) {
            return;
        }

        Mail::to($event->order->customer_email)
            ->send(new OrderStatusChangedEmail($event->order));
    }
}
