<?php

namespace Modules\Beautician\Providers;

use Modules\Beautician\Admin\BeauticianTabs;
use Modules\Beautician\Entities\Beautician;
use Modules\Beautician\Observers\BeauticianObserver;
use Illuminate\Support\ServiceProvider;
use Modules\Admin\Ui\Facades\TabManager;

class BeauticianServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        TabManager::register('beauticians', BeauticianTabs::class);

        Beautician::observe(BeauticianObserver::class);
    }
}
