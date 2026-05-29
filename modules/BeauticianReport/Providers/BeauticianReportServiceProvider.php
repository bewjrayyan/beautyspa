<?php

namespace Modules\BeauticianReport\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class BeauticianReportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('beauticianreport::admin.reports.*', function ($view) {
            $view->with('request', $this->app['request']);
        });
    }
}
