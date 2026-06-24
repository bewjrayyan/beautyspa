<?php

namespace Modules\GoogleIntegration\Providers;

use Modules\GoogleIntegration\Console\BackfillGoogleSheetsCommand;
use Modules\GoogleIntegration\Console\RetryFailedGoogleSheetsSyncCommand;
use Modules\GoogleIntegration\Support\GoogleSheetsColumnConfig;
use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Events\OrderUpdated;
use Modules\GoogleIntegration\Listeners\SyncOrderToGoogle;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class GoogleIntegrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'googleintegration');

        GoogleSheetsStatusConfig::applyMissingOnly();
        GoogleSheetsColumnConfig::applyMissingOnly();

        Event::listen([
            OrderStatusChanged::class,
            OrderCreated::class,
            OrderUpdated::class,
        ], SyncOrderToGoogle::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                BackfillGoogleSheetsCommand::class,
                RetryFailedGoogleSheetsSyncCommand::class,
            ]);
        }
    }
}
