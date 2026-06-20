<?php

namespace AestheticCart\Console\Commands;

use Mcamara\LaravelLocalization\Commands\RouteTranslationsCacheCommand as BaseRouteTranslationsCacheCommand;

class RouteTranslationsCacheCommand extends BaseRouteTranslationsCacheCommand
{
    public function handle()
    {
        $this->callSilent('route:trans:clear');

        $this->components->error(
            'Translated route caching is disabled for AestheticCart. Module storefront routes (including `home`) are not registered during route:trans:cache, which breaks pages and 404 handling.'
        );

        $this->components->warn('Use `php artisan optimize` (config + view cache only) after deploy instead.');

        return self::FAILURE;
    }
}
