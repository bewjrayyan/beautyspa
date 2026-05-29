<?php

namespace Modules\GoogleIntegration\Providers;

use Modules\Order\Events\OrderStatusChanged;
use Modules\GoogleIntegration\Listeners\SyncCompletedOrderToGoogle;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class GoogleIntegrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(OrderStatusChanged::class, SyncCompletedOrderToGoogle::class);
    }
}
