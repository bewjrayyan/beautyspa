<?php

namespace Modules\GoogleIntegration\Listeners;

use Modules\GoogleIntegration\Jobs\SyncOrderToGoogleJob;
use Modules\GoogleIntegration\Services\GoogleServiceAccountClient;
use Modules\GoogleIntegration\Services\GoogleSheetsService;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Events\OrderUpdated;

class SyncOrderToGoogle
{
    public function handle(OrderStatusChanged|OrderCreated|OrderUpdated $event): void
    {
        if (
            $event instanceof OrderStatusChanged
            && ($event->changeType ?: 'order') === 'treatment'
        ) {
            // Treatment controllers already fire OrderUpdated for sheet/calendar sync.
            return;
        }

        if (! GoogleServiceAccountClient::isConfigured()) {
            return;
        }

        if (! setting('google_sheets_enabled') && ! setting('google_calendar_enabled')) {
            return;
        }

        if (
            setting('google_sheets_enabled')
            && ! GoogleSheetsService::isEnabled()
            && ! setting('google_calendar_enabled')
        ) {
            return;
        }

        SyncOrderToGoogleJob::dispatch(
            $event->order->id,
            forceSheets: false,
            trigger: $this->triggerFor($event),
        )->afterCommit();
    }


    private function triggerFor(OrderStatusChanged|OrderCreated|OrderUpdated $event): string
    {
        return match (true) {
            $event instanceof OrderCreated => 'created',
            $event instanceof OrderUpdated => 'updated',
            default => 'status',
        };
    }
}
