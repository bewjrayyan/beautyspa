<?php

namespace Modules\GoogleIntegration\Listeners;

use Exception;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\GoogleIntegration\Services\CompletedOrderGoogleSync;
use Modules\GoogleIntegration\Services\GoogleServiceAccountClient;

class SyncCompletedOrderToGoogle
{
    public function __construct(
        private readonly CompletedOrderGoogleSync $sync,
    ) {
    }


    public function handle(OrderStatusChanged $event): void
    {
        if ($event->order->status !== Order::COMPLETED) {
            return;
        }

        if (! GoogleServiceAccountClient::isConfigured()) {
            return;
        }

        if (! setting('google_sheets_enabled') && ! setting('google_calendar_enabled')) {
            return;
        }

        try {
            $this->sync->sync($event->order->fresh());
        } catch (Exception $exception) {
            report($exception);
        }
    }
}
