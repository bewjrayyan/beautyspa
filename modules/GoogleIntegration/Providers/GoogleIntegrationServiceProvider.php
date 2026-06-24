<?php

namespace Modules\GoogleIntegration\Providers;

use Modules\GoogleIntegration\Console\BackfillGoogleSheetsCommand;
use Modules\GoogleIntegration\Console\RetryFailedGoogleSheetsSyncCommand;
use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;
use Modules\Order\Events\OrderStatusChanged;
use Modules\GoogleIntegration\Listeners\SyncCompletedOrderToGoogle;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class GoogleIntegrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'googleintegration');

        GoogleSheetsStatusConfig::applyMissingOnly();

        Event::listen(OrderStatusChanged::class, SyncCompletedOrderToGoogle::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                BackfillGoogleSheetsCommand::class,
                RetryFailedGoogleSheetsSyncCommand::class,
            ]);
        }
    }
}
