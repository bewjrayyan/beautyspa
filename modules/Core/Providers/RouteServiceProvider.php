<?php

namespace Modules\Core\Providers;

use Closure;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define the routes for the application.
     */
    public function map()
    {
        if (config('app.installed')) {
            $this->mapPwaRoutes();
            $this->mapModuleRoutes();
            $this->mapLegacyLocalizedAdminRedirects();
        }
    }


    /**
     * PWA routes without locale prefix (service worker expects /fleetcart/offline, not /fleetcart/en/offline).
     *
     * @return void
     */
    private function mapPwaRoutes(): void
    {
        Route::middleware('web')->group(function () {
            Route::get('manifest.json', [\Modules\Support\Http\Controllers\ManifestController::class, 'json'])
                ->name('manifest.json');

            Route::get('offline', [\Modules\Support\Http\Controllers\ManifestController::class, 'offline'])
                ->name('offline');
        });

        $this->mapUtilityRoutes();
    }


    /**
     * Locale-agnostic JSON/helpers (axios must not prefix /{locale} on these paths).
     */
    private function mapUtilityRoutes(): void
    {
        Route::middleware('web')->group(function () {
            Route::get('favicon.ico', [\Modules\Support\Http\Controllers\FaviconController::class, 'show'])
                ->name('favicon');

            Route::get('countries/{code}/states', [\Modules\Support\Http\Controllers\CountryStateController::class, 'index'])
                ->name('countries.states.index');
        });
    }


    /**
     * Map routes from all enabled modules.
     *
     * @return void
     */
    private function mapModuleRoutes()
    {
        foreach ($this->app['modules']->allEnabled() as $module) {
            $namespace = "Modules\\{$module->getName()}\\Http\\Controllers";

            $this->mapAdminRoutes("{$module->getPath()}/Routes/admin.php", $namespace);
        }

        foreach ($this->app['modules']->allEnabled() as $module) {
            $namespace = "Modules\\{$module->getName()}\\Http\\Controllers";

            $this->groupPublicRoutes($namespace, function () use ($module) {
                $this->mapPublicRoutes("{$module->getPath()}/Routes/public.php");
                $this->mapApiRoutes("{$module->getPath()}/Routes/api.php");
            });
        }
    }


    /**
     * Admin panel routes live at /admin/* (no storefront locale prefix).
     *
     * @return void
     */
    private function mapAdminRoutes(string $path, string $namespace): void
    {
        if (! file_exists($path)) {
            return;
        }

        Route::group([
            'namespace' => "{$namespace}\\Admin",
            'prefix' => 'admin',
            'middleware' => ['web', 'admin_locale', 'admin', 'licensed', 'beautician.portal.restrict'],
        ], function () use ($path) {
            require_once $path;
        });
    }


    /**
     * Storefront routes keep the /{locale}/ prefix (en, ms, …).
     *
     * @return void
     */
    private function groupPublicRoutes(string $namespace, Closure $callback): void
    {
        Route::group([
            'namespace' => $namespace,
            'prefix' => LaravelLocalization::setLocale(),
            'middleware' => [
                // Outermost: must wrap locale redirects (locale_session returns early).
                'fix_subdirectory_localized_redirect',
                'localize',
                'locale_session_redirect',
                'localization_redirect',
                'web',
            ],
        ], $callback);
    }


    /**
     * Map public routes.
     *
     * @return void
     */
    private function mapPublicRoutes($path)
    {
        if (file_exists($path)) {
            require_once $path;
        }
    }


    private function mapApiRoutes($path)
    {
        if (! file_exists($path)) {
            return;
        }

        Route::group([
            'namespace' => 'Api',
            'prefix' => 'api',
            'middleware' => ['api'],
        ], function () use ($path) {
            require_once $path;
        });
    }


    /**
     * Redirect old localized admin URLs (/en/admin/…) to /admin/….
     *
     * @return void
     */
    private function mapLegacyLocalizedAdminRedirects(): void
    {
        $locales = implode('|', array_map(
            fn (string $locale) => preg_quote($locale, '/'),
            supported_locale_keys()
        ));

        if ($locales === '') {
            return;
        }

        Route::middleware('web')->group(function () use ($locales) {
            Route::get('{locale}/admin', function () {
                return redirect()->route('admin.login', status: 301);
            })->where(['locale' => $locales]);

            Route::get('{locale}/admin/{path}', function (string $locale, string $path) {
                return redirect('/admin/' . $path, 301);
            })->where([
                'locale' => $locales,
                'path' => '.*',
            ]);
        });
    }
}
