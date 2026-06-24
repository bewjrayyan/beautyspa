<?php

namespace Modules\GoogleIntegration\Listeners;

use Exception;
use Modules\GoogleIntegration\Services\GoogleServiceAccountClient;
use Modules\GoogleIntegration\Services\GoogleSheetsService;
use Modules\GoogleIntegration\Services\OrderGoogleSyncService;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Events\OrderUpdated;

class SyncOrderToGoogle
{
    public function __construct(
        private readonly OrderGoogleSyncService $sync,
    ) {
    }


    public function handle(OrderStatusChanged|OrderCreated|OrderUpdated $event): void
    {
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

        try {
            $this->sync->sync($event->order->fresh());
        } catch (Exception $exception) {
            report($exception);
        }
    }
}
