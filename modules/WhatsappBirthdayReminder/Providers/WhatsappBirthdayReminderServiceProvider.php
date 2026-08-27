<?php

namespace Modules\WhatsappBirthdayReminder\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\WhatsappBirthdayReminder\Console\GrantPermissionsCommand;
use Modules\WhatsappBirthdayReminder\Console\SendBirthdayRemindersCommand;

class WhatsappBirthdayReminderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! config('app.installed')) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                SendBirthdayRemindersCommand::class,
                GrantPermissionsCommand::class,
            ]);
        }
    }
}
