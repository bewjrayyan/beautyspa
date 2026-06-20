<?php

namespace AestheticCart\Console\Commands;

use Illuminate\Foundation\Console\RouteCacheCommand as BaseRouteCacheCommand;

class RouteCacheCommand extends BaseRouteCacheCommand
{
    public function handle()
    {
        $this->callSilent('route:clear');

        $this->components->error(
            'Route caching is disabled for AestheticCart. Module storefront routes (including `home`) are not registered during route:cache, which breaks pages and 404 handling.'
        );

        $this->components->warn('Use `php artisan optimize --except=routes` (or config:cache + view:cache) after deploy instead.');

        return self::FAILURE;
    }
}
