<?php

namespace Modules\Support\Cache;

use Illuminate\Support\Facades\Cache;
use Throwable;

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
            if (! self::probePlainStore()) {
                self::disableCache();

                return;
            }
        }

        if (! self::probeTaggedStore()) {
            self::purgeCorruptTagFiles('_aestheticcart_probe');
            self::purgeCorruptTagFiles('aestheticcart');
            self::disableCache();
        }
    }

    private static function probePlainStore(): bool
    {
        return self::withSuppressedWarnings(function () {
            Cache::store()->put('_aestheticcart_cache_probe', 1, 10);
            Cache::store()->forget('_aestheticcart_cache_probe');

            return true;
        });
    }

    private static function probeTaggedStore(): bool
    {
        return self::withSuppressedWarnings(function () {
            Cache::tags('_aestheticcart_probe')->put('_tag_probe', 1, 10);
            Cache::tags('_aestheticcart_probe')->forget('_tag_probe');

            return true;
        });
    }

    /**
     * Run a cache probe without letting vendor warnings become ALERT logs.
     * FilesystemCachePool can emit foreach() warnings on corrupt tag lists.
     */
    private static function withSuppressedWarnings(callable $callback): bool
    {
        set_error_handler(static function (int $severity, string $message): bool {
            if ($severity === E_WARNING || $severity === E_USER_WARNING || $severity === E_NOTICE) {
                throw new \ErrorException($message, 0, $severity);
            }

            return false;
        });

        try {
            return (bool) $callback();
        } catch (Throwable) {
            return false;
        } finally {
            restore_error_handler();
        }
    }

    private static function disableCache(): void
    {
        config([
            'app.cache' => false,
            'cache.default' => 'array',
        ]);
    }

    private static function purgeCorruptTagFiles(string $tag): void
    {
        $cachePath = (string) config('cache.stores.file.path', storage_path('framework/cache/data'));

        if (! is_dir($cachePath)) {
            return;
        }

        $needles = [
            'tag!'.$tag,
            'tag!_'.$tag,
        ];

        foreach (scandir($cachePath) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            // Only touch filesystem tag-index files, never regular cache payloads.
            if (! str_contains($entry, 'tag!')) {
                continue;
            }

            foreach ($needles as $needle) {
                if (str_contains($entry, $needle)) {
                    @unlink($cachePath.DIRECTORY_SEPARATOR.$entry);
                    break;
                }
            }
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
        $cacheDriver = (string) config('cache.default', 'file');
        $sessionDriver = (string) config('session.driver', 'file');

        if ($cacheDriver === 'redis' || $sessionDriver === 'redis') {
            return true;
        }

        return $sessionDriver === 'cache' && $cacheDriver === 'redis';
    }

    private static function isRedisReachable(): bool
    {
        try {
            return (bool) app('redis')->connection()->ping();
        } catch (Throwable) {
            return false;
        }
    }
}
