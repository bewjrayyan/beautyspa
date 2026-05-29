<?php

namespace Modules\Sms\Providers;

use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Legacy Twilio/Vonage gateways removed — all notifications use OneSender WhatsApp API.
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}
