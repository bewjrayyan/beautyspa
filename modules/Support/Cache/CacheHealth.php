<?php

namespace Modules\Support\Cache;

use Illuminate\Support\Facades\Cache;

class CacheHealth
{
    public static function apply(): void
    {
        if (! config('app.installed') || ! config('app.cache')) {
            return;
        }

        try {
            Cache::store()->put('_aestheticcart_cache_probe', 1, 10);
            Cache::store()->forget('_aestheticcart_cache_probe');
        } catch (\Throwable) {
            config(['app.cache' => false]);

            return;
        }

        try {
            Cache::tags('_aestheticcart_probe')->put('_tag_probe', 1, 10);
            Cache::tags('_aestheticcart_probe')->forget('_tag_probe');
        } catch (\Throwable) {
            config(['app.cache' => false]);
        }
    }
}
