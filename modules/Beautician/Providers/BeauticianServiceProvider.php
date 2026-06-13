<?php

namespace Modules\Beautician\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Beautician\Admin\BeauticianJobTitleTabs;
use Modules\Beautician\Admin\BeauticianTabs;
use Modules\Beautician\Console\GrantBeauticianPermissionsCommand;
use Modules\Beautician\Console\SyncJobTitlesCommand;
use Modules\Beautician\Entities\Beautician;
use Modules\Beautician\Observers\BeauticianObserver;

class BeauticianServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        TabManager::register('beauticians', BeauticianTabs::class);
        TabManager::register('beautician_job_titles', BeauticianJobTitleTabs::class);

        Beautician::observe(BeauticianObserver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                GrantBeauticianPermissionsCommand::class,
                SyncJobTitlesCommand::class,
            ]);
        }
    }
}
