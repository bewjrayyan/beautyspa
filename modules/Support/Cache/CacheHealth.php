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

        if (self::usesRedis() && ! self::isRedisReachable()) {
            self::fallbackFromRedis();

            return;
        }

        if (! self::usesRedis()) {
            try {
                Cache::store()->put('_aestheticcart_cache_probe', 1, 10);
                Cache::store()->forget('_aestheticcart_cache_probe');
            } catch (\Throwable) {
                config(['app.cache' => false]);

                return;
            }
        }

        try {
            Cache::tags('_aestheticcart_probe')->put('_tag_probe', 1, 10);
            Cache::tags('_aestheticcart_probe')->forget('_tag_probe');
        } catch (\Throwable) {
            config(['app.cache' => false]);
        }
    }

    public static function fallbackFromRedis(): void
    {
        config([
            'app.cache' => false,
            'cache.default' => 'array',
            'session.driver' => 'file',
        ]);
    }

    private static function usesRedis(): bool
    {
        $cacheDriver = (string) config('cache.default', env('CACHE_DRIVER', 'file'));
        $sessionDriver = (string) config('session.driver', env('SESSION_DRIVER', 'file'));

        if ($cacheDriver === 'redis' || $sessionDriver === 'redis') {
            return true;
        }

        return $sessionDriver === 'cache' && $cacheDriver === 'redis';
    }

    private static function isRedisReachable(): bool
    {
        try {
            return (bool) app('redis')->connection()->ping();
        } catch (\Throwable) {
            return false;
        }
    }
}
