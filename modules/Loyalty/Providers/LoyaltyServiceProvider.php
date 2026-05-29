<?php

namespace Modules\Loyalty\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Order\Events\OrderStatusChanged;
use Modules\User\Events\CustomerRegistered;
use Modules\Loyalty\Console\ExpireLoyaltyPointsCommand;
use Modules\Loyalty\Console\AwardBirthdayBonusCommand;
use Modules\Loyalty\Console\GrantLoyaltyPermissionsCommand;
use Modules\Loyalty\Console\NotifyExpiringLoyaltyPointsCommand;
use Modules\Loyalty\Console\RecalculateLifetimeSpendCommand;
use Modules\Loyalty\Console\SyncReferralCodesCommand;
use Modules\Loyalty\Console\SyncLoyaltyTranslationsCommand;
use Modules\Loyalty\Listeners\CreateWalletOnCustomerRegistered;
use Modules\Loyalty\Listeners\ProcessReferralOnCustomerRegistered;
use Modules\Loyalty\Listeners\ProcessLoyaltyOnOrderStatusChanged;

class LoyaltyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path('Loyalty', 'Config/settings_defaults.php'),
            'loyalty.settings_defaults'
        );
    }

    public function boot(): void
    {
        $this->app['events']->listen(
            OrderStatusChanged::class,
            ProcessLoyaltyOnOrderStatusChanged::class
        );

        $this->app['events']->listen(
            CustomerRegistered::class,
            CreateWalletOnCustomerRegistered::class
        );

        $this->app['events']->listen(
            CustomerRegistered::class,
            ProcessReferralOnCustomerRegistered::class
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                ExpireLoyaltyPointsCommand::class,
                GrantLoyaltyPermissionsCommand::class,
                NotifyExpiringLoyaltyPointsCommand::class,
                AwardBirthdayBonusCommand::class,
                SyncReferralCodesCommand::class,
                RecalculateLifetimeSpendCommand::class,
                SyncLoyaltyTranslationsCommand::class,
            ]);
        }
    }
}
