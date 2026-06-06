<?php

namespace Modules\SpaBranch\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\SpaBranch\Admin\SpaBranchTabs;
use Modules\SpaBranch\Console\BackfillOrderSpaBranchCommand;
use Modules\SpaBranch\Console\GrantSpaBranchPermissionsCommand;

class SpaBranchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        TabManager::register('spa_branches', SpaBranchTabs::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                GrantSpaBranchPermissionsCommand::class,
                BackfillOrderSpaBranchCommand::class,
            ]);
        }
    }
}
