<?php

namespace Modules\Core\Providers;

use Exception;
use Throwable;
use Mcamara\LaravelLocalization\Exceptions\UnsupportedLocaleException;
use Modules\Core\Support\WritableStorageBootstrap;
use Modules\Support\Locale;
use Modules\Setting\Entities\Setting;
use Modules\Support\Cache\CacheHealth;
use Illuminate\Support\ServiceProvider;
use AestheticCart\Http\Middleware\LicenseChecker;
use Modules\Core\Http\Middleware\Authenticate;
use Modules\Core\Http\Middleware\Authorization;
use Modules\Core\Http\Middleware\GuestMiddleware;
use Modules\Core\Http\Middleware\AdminMiddleware;
use Modules\Core\Http\Middleware\ApplySessionLocale;
use Modules\Setting\Repositories\SettingRepository;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Core module specific middleware.
     *
     * @var array
     */
    protected $middleware = [
        'auth' => Authenticate::class,
        'admin' => AdminMiddleware::class,
        'admin_locale' => ApplySessionLocale::class,
        'licensed' => LicenseChecker::class,
        'guest' => GuestMiddleware::class,
        'can' => Authorization::class,
        'localize' => LaravelLocalizationRoutes::class,
        'locale_session_redirect' => LocaleSessionRedirect::class,
        'fix_subdirectory_localized_redirect' => \Modules\Core\Http\Middleware\FixSubdirectoryLocalizedRedirect::class,
        'localization_redirect' => LaravelLocalizationRedirectFilter::class,
    ];


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        if (! config('app.installed')) {
            return;
        }

        $this->seedFallbackLocalizationConfig();
        $this->prepareWritableStorage();
        $this->prepareCacheStorePath();
    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!config('app.installed')) {
            return;
        }

        CacheHealth::apply();

        $this->prepareWritableStorage();
        $this->prepareCacheStorePath();
        $this->setupSupportedLocales();
        $this->registerSetting();
        $this->setupAppLocale();
        $this->hideDefaultLocaleInURL();
        $this->ignoreAdminUrlsFromLocalization();
        $this->setupAppTimezone();
        $this->setupMailConfig();
        $this->registerMiddleware();
        $this->registerInAdminPanelState();
    }


    /**
     * Setup supported locales.
     *
     * @return void
     */
    private function setupSupportedLocales()
    {
        $locales = $this->getSupportedLocales();
        $defaultLocale = $this->getDefaultLocale();

        if (! in_array($defaultLocale, $locales, true)) {
            array_unshift($locales, $defaultLocale);
        }

        $supportedLocales = [];

        foreach ($locales as $locale) {
            $supportedLocales[$locale]['name'] = locale_display_name($locale);
        }

        $this->app['config']->set('laravellocalization.supportedLocales', $supportedLocales);
    }


    /**
     * Get supported locales from database.
     *
     * @return array
     */
    private function getSupportedLocales()
    {
        $fallback = ['en', 'ms'];

        try {
            $locales = Setting::get('supported_locales', $fallback);
        } catch (Throwable $e) {
            return $fallback;
        }

        if (! is_array($locales)) {
            return $fallback;
        }

        $locales = array_values(array_filter($locales, fn ($locale) => is_string($locale) && $locale !== ''));

        return $locales !== [] ? $locales : $fallback;
    }


    private function getDefaultLocale(): string
    {
        try {
            $defaultLocale = Setting::get('default_locale', 'en');
        } catch (Throwable $e) {
            return 'en';
        }

        if (! is_string($defaultLocale) || $defaultLocale === '') {
            return 'en';
        }

        return $defaultLocale;
    }


    private function seedFallbackLocalizationConfig(): void
    {
        $supportedLocales = $this->app['config']->get('laravellocalization.supportedLocales', []);

        if ($supportedLocales !== []) {
            return;
        }

        $this->app['config']->set('laravellocalization.supportedLocales', [
            'en' => ['name' => 'English'],
            'ms' => ['name' => 'Bahasa Melayu'],
        ]);
    }


    /**
     * Register setting binding.
     *
     * @return void
     */
    private function registerSetting()
    {
        $this->app->singleton('setting', function () {
            return new SettingRepository(Setting::allCached());
        });
    }


    /**
     * Setup application locale.
     *
     * @return string
     */
    private function setupAppLocale()
    {
        $defaultLocale = $this->getDefaultLocale();
        $supportedLocaleKeys = array_keys($this->app['config']->get('laravellocalization.supportedLocales', []));

        if ($supportedLocaleKeys !== [] && ! in_array($defaultLocale, $supportedLocaleKeys, true)) {
            $defaultLocale = $supportedLocaleKeys[0];
        }

        $this->app['config']->set('app.locale', $defaultLocale);
        $this->app['config']->set('app.fallback_locale', $defaultLocale);

        try {
            $locale = is_null(LaravelLocalization::setLocale()) ? $defaultLocale : null;

            LaravelLocalization::setLocale($locale);
        } catch (UnsupportedLocaleException|Throwable $e) {
            // Keep booting; storefront locale middleware will resolve on the request.
        }
    }


    /**
     * Ensure logs and cache are writable on XAMPP/macOS (Apache user != CLI user).
     *
     * @return void
     */
    private function prepareWritableStorage(): void
    {
        WritableStorageBootstrap::apply();
    }


    /**
     * Configure file cache path before Setting::allCached() runs.
     *
     * @return void
     */
    private function prepareCacheStorePath(): void
    {
        if (! config('app.cache')) {
            $this->app['config']->set('cache.default', 'array');

            return;
        }

        $driver = env('CACHE_DRIVER', 'file');
        $this->app['config']->set('cache.default', $driver);

        if ($driver !== 'file') {
            return;
        }

        $path = $this->resolveFileCachePath();

        if (! is_dir($path)) {
            @mkdir($path, 0777, true);
        } elseif ($this->app->environment('local')) {
            @chmod($path, 0777);
        }

        $this->app['config']->set('cache.stores.file.path', $path);
    }


    private function resolveFileCachePath(): string
    {
        if ($this->app->environment('local') || WritableStorageBootstrap::isLocalEnvironment()) {
            return WritableStorageBootstrap::fileCachePath();
        }

        if ($this->app->runningInConsole()) {
            return storage_path('framework/cache/cli-data');
        }

        return storage_path('framework/cache/data');
    }


    /**
     * Hide default locale in url for non multi-locale mode.
     *
     * @return void
     */
    private function hideDefaultLocaleInURL()
    {
        if (!is_multilingual()) {
            $this->app['config']->set('laravellocalization.hideDefaultLocaleInURL', true);
        }
    }


    /**
     * Admin URLs are not prefixed with storefront locale segments.
     *
     * @return void
     */
    private function ignoreAdminUrlsFromLocalization()
    {
        $ignored = $this->app['config']->get('laravellocalization.urlsIgnored', []);

        $this->app['config']->set('laravellocalization.urlsIgnored', array_values(array_unique(array_merge(
            $ignored,
            ['/admin', '/admin/*']
        ))));
    }


    /**
     * Setup application timezone.
     *
     * @return void
     */
    private function setupAppTimezone()
    {
        $timezone = setting('default_timezone') ?? config('app.timezone');

        date_default_timezone_set($timezone);

        $this->app['config']->set('app.timezone', $timezone);
    }


    /**
     * Setup application mail config.
     *
     * @return void
     */
    private function setupMailConfig()
    {
        $host = trim((string) (setting('mail_host') ?: env('MAIL_HOST', '')));

        $this->app['config']->set('mail.from.address', setting('mail_from_address') ?: env('MAIL_FROM_ADDRESS', 'hello@example.com'));
        $this->app['config']->set('mail.from.name', setting('mail_from_name') ?: env('MAIL_FROM_NAME', 'AestheticCart'));

        if ($host === '') {
            $this->app['config']->set('mail.default', 'log');

            return;
        }

        $this->app['config']->set('mail.default', 'smtp');
        $this->app['config']->set('mail.mailers.smtp.host', $host);
        $this->app['config']->set('mail.mailers.smtp.port', setting('mail_port') ?: env('MAIL_PORT', 587));
        $this->app['config']->set('mail.mailers.smtp.username', setting('mail_username') ?: env('MAIL_USERNAME'));
        $this->app['config']->set('mail.mailers.smtp.password', setting('mail_password') ?: env('MAIL_PASSWORD'));
        $this->app['config']->set('mail.mailers.smtp.encryption', setting('mail_encryption') ?: env('MAIL_ENCRYPTION'));
    }


    /**
     * Register the filters.
     *
     * @return void
     */
    private function registerMiddleware()
    {
        foreach ($this->middleware as $name => $middleware) {
            $this->app['router']->aliasMiddleware($name, $middleware);
        }
    }


    /**
     * Register inAdminPanel state to the IoC container.
     *
     * @return void
     */
    private function registerInAdminPanelState()
    {
        if ($this->app->runningInConsole()) {
            return $this->app['inAdminPanel'] = false;
        }

        $path = trim($this->app['request']->path(), '/');

        $this->app['inAdminPanel'] = $path === 'admin'
            || str_starts_with($path, 'admin/');
    }
}
