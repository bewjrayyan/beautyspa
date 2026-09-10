<?php

namespace Modules\Lead\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Lead\Console\GrantLeadPermissionsCommand;

class LeadServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GrantLeadPermissionsCommand::class,
            ]);
        }
    }
}
