<?php

namespace Modules\Order\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Order\Console\ImportWordPressOrdersCommand;
use Modules\Order\Console\SyncOrderTranslationsCommand;
use Modules\Order\Entities\Order;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportWordPressOrdersCommand::class,
                SyncOrderTranslationsCommand::class,
            ]);
        }

        View::composer('order::admin.orders.partials.order_and_account_information', function ($view) {
            $order = $view->getData()['order'] ?? null;

            if (! $order instanceof Order) {
                return;
            }

            $loyaltyWallet = null;

            if (app('modules')->isEnabled('Loyalty') && $order->customer_id) {
                $loyaltyWallet = LoyaltyWallet::query()
                    ->with('tier')
                    ->where('user_id', $order->customer_id)
                    ->first();
            }

            $view->with('loyaltyWallet', $loyaltyWallet);
        });
    }
}
