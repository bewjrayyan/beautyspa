<?php

namespace Modules\Order\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Order\Console\ImportWordPressOrdersCommand;
use Modules\Order\Console\SyncOrderTranslationsCommand;
use Modules\Order\Console\SecureOrderDocumentsCommand;
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
        if (config('app.installed')) {
            // Locale-free signed URLs — must not sit under /{locale} or localization_redirect invalidates signatures.
            Route::middleware('web')
                ->namespace('Modules\Order\Http\Controllers')
                ->group(module_path('Order', 'Routes/secure.php'));
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportWordPressOrdersCommand::class,
                SyncOrderTranslationsCommand::class,
                SecureOrderDocumentsCommand::class,
            ]);
        }

        View::composer([
            'order::admin.orders.partials.order_and_account_information',
            'order::admin.orders.partials.order_totals',
            'order::admin.orders.partials.order_summary_order_information',
        ], function ($view) {
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
