<?php

namespace Modules\Report\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Report\Console\SyncReportTranslationsCommand;
use Modules\Report\Services\ReportDashboardService;
use Nwidart\Modules\Facades\Module;

class ReportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncReportTranslationsCommand::class,
            ]);
        }

        View::composer('report::admin.reports.*', function ($view) {
            $view->with('request', $this->app['request']);
        });

        View::composer('report::admin.reports.layout', function ($view) {
            $service = app(ReportDashboardService::class);
            $isBookingsReport = request()->query('type') === 'beautician_bookings_report';

            $view->with([
                'reportLayoutMode' => $isBookingsReport ? 'bookings' : 'full',
                'reportDashboard' => $isBookingsReport ? [] : $service->overview(),
                'bookingStats' => $isBookingsReport ? $service->bookingPageStats() : [],
                'beauticianBookings' => $isBookingsReport ? collect() : $service->beauticianBookings(12),
                'showBeauticianAnalytics' => Module::isEnabled('Beautician'),
                'beauticianAnalyticsUrl' => Module::isEnabled('BeauticianReport')
                    ? route('admin.beautician_reports.index')
                    : null,
            ]);
        });
    }
}
