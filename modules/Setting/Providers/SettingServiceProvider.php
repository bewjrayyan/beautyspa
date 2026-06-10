<?php

namespace Modules\Setting\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Setting\Admin\SettingTabs;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            module_path('Setting', 'Config/whatsapp_notifications.php'),
            'setting.whatsapp_notifications'
        );

        $this->mergeConfigFrom(
            module_path('Setting', 'Config/app_version.php'),
            'setting.app_version'
        );

        $catalogSyncConfig = module_path('Setting', 'Config/catalog_sync.php');

        if (is_file($catalogSyncConfig)) {
            $this->mergeConfigFrom($catalogSyncConfig, 'setting.catalog_sync');
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        TabManager::register('settings', SettingTabs::class);

        View::composer([
            'setting::admin.settings.tabs.*',
            'setting::admin.settings.partials.*',
        ], function ($view) {
            if (! $view->offsetExists('errors')) {
                try {
                    $errors = request()->hasSession()
                        ? (request()->session()->get('errors') ?: new ViewErrorBag())
                        : new ViewErrorBag();
                } catch (\Throwable) {
                    $errors = new ViewErrorBag();
                }

                $view->with('errors', $errors);
            }

            if (! $view->offsetExists('settings')) {
                try {
                    $view->with('settings', setting()->all());
                } catch (\Throwable) {
                    $view->with('settings', []);
                }
            }
        });
    }
}
