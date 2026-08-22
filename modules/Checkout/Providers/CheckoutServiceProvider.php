<?php

namespace Modules\Checkout\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Checkout\Console\ExpireStalePendingCheckoutOrdersCommand;

class CheckoutServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExpireStalePendingCheckoutOrdersCommand::class,
            ]);
        }
    }
}
