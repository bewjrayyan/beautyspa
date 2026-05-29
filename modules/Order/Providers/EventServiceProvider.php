<?php

namespace Modules\Order\Providers;

use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Listeners\SendOrderStatusChangedSms;
use Modules\Order\Listeners\SendCompletedOrderBeauticianWhatsApp;
use Modules\Order\Listeners\SendCompletedOrderGroupWhatsApp;
use Modules\Order\Listeners\SendOrderStatusChangedEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderStatusChanged::class => [
            SendOrderStatusChangedEmail::class,
            SendOrderStatusChangedSms::class,
            SendCompletedOrderGroupWhatsApp::class,
            SendCompletedOrderBeauticianWhatsApp::class,
        ],
    ];
}
