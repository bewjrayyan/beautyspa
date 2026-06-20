<?php

namespace AestheticCart\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ApplyStorefrontDefaultsCommand::class,
        Commands\RestoreAdminSettingsCommand::class,
        Commands\SyncAdminModulePermissionsCommand::class,
        Commands\RestoreDatabaseCommand::class,
        Commands\SeedDemoDataCommand::class,
        Commands\ExportDemoDataCommand::class,
        Commands\ImportDemoDataCommand::class,
        Commands\ScaffoldModuleCommand::class,
        Commands\ScaffoldEntityCommand::class,
        Commands\RouteCacheCommand::class,
        Commands\RouteTranslationsCacheCommand::class,
        Commands\OptimizeCommand::class,
    ];


    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        if (app('modules')->isEnabled('Loyalty')) {
            $schedule->command('loyalty:expire-points')->dailyAt('02:00');
            $schedule->command('loyalty:notify-expiring')->dailyAt('09:00');
            $schedule->command('loyalty:award-birthday-bonus')->dailyAt('08:00');
        }

        if (app('modules')->isEnabled('TreatmentReservation')) {
            $schedule->command('treatment-reservations:send-appointment-reminders')->everyFifteenMinutes();
            $schedule->command('treatment-reservations:send-customer-appointment-reminders')->everyFifteenMinutes();
            $schedule->command('treatment-reservations:send-customer-followups')->dailyAt('10:00');
        }

        $schedule->command('onesender:process-outbound-queue')->everyMinute();
    }
}
