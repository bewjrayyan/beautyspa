<?php

namespace Modules\GoogleIntegration\Providers;

use Modules\GoogleIntegration\Console\BackfillGoogleCalendarCommand;
use Modules\GoogleIntegration\Console\BackfillGoogleSheetsCommand;
use Modules\GoogleIntegration\Console\RetryFailedGoogleSheetsSyncCommand;
use Modules\GoogleIntegration\Support\GoogleSheetsColumnConfig;
use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Events\OrderUpdated;
use Modules\GoogleIntegration\Listeners\SyncOrderToGoogle;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\GoogleIntegration\Services\GoogleSheetsService;
use Modules\GoogleIntegration\Services\GoogleSheetsSyncLogExporter;
use Modules\Order\Entities\Order;

class GoogleIntegrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'googleintegration');

        // Never seed settings from queue:work / artisan — workers reboot often and
        // must not hit MySQL just to fill missing Google Sheets keys.
        if (! $this->app->runningInConsole()) {
            try {
                GoogleSheetsStatusConfig::applyMissingOnly();
                GoogleSheetsColumnConfig::applyMissingOnly();
            } catch (\Throwable) {
                // Settings seeding is best-effort; never block app boot.
            }
        }

        View::composer('admin::dashboard.index', function ($view) {
            if (! GoogleSheetsService::isEnabled()) {
                return;
            }

            $failedCount = GoogleSheetsSyncLogExporter::failedOrdersCount();

            $view->with([
                'showGoogleSheetsStats' => true,
                'googleSheetsFailedCount' => $failedCount,
                'googleSheetsFailedUrl' => route('admin.orders.index', ['google_sheets_failed' => 1]),
                'googleSheetsSettingsUrl' => route('admin.settings.edit', ['tab' => 'google_sheets']),
            ]);
        });

        Event::listen([
            OrderStatusChanged::class,
            OrderCreated::class,
            OrderUpdated::class,
        ], SyncOrderToGoogle::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                BackfillGoogleSheetsCommand::class,
                BackfillGoogleCalendarCommand::class,
                RetryFailedGoogleSheetsSyncCommand::class,
            ]);
        }
    }
}
