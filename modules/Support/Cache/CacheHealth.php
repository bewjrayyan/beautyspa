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

        // Concurrent artisan/cron boots race on cli-data filesystem keys and spam ALERTs.
        // Redis reachability is still checked above; file/tag probes are web-request only.
        if (app()->runningInConsole() && ! self::usesRedis()) {
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
            $key = '_aestheticcart_cache_probe';

            Cache::store()->put($key, 1, 10);

            if (Cache::store()->get($key) !== 1) {
                return false;
            }

            // Missing file on forget is a race, not a failed probe.
            try {
                Cache::store()->forget($key);
            } catch (Throwable) {
                // ignore
            }

            return true;
        });
    }

    private static function probeTaggedStore(): bool
    {
        return self::withSuppressedWarnings(function () {
            Cache::tags('_aestheticcart_probe')->put('_tag_probe', 1, 10);

            if (Cache::tags('_aestheticcart_probe')->get('_tag_probe') !== 1) {
                return false;
            }

            try {
                Cache::tags('_aestheticcart_probe')->forget('_tag_probe');
            } catch (Throwable) {
                // ignore
            }

            return true;
        });
    }

    /**
     * Swallow Flysystem/cache-adapter warnings without converting them to exceptions.
     * Throwing ErrorException caused CachePoolException ALERT spam on missing probe files.
     */
    private static function withSuppressedWarnings(callable $callback): bool
    {
        set_error_handler(static function (int $severity): bool {
            return in_array($severity, [E_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE], true);
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
        $paths = array_unique(array_filter([
            (string) config('cache.stores.file.path', storage_path('framework/cache/data')),
            storage_path('framework/cache/data'),
            storage_path('framework/cache/cli-data'),
            storage_path('framework/cache/cli-data/cache'),
        ]));

        $needles = [
            'tag!'.$tag,
            'tag!_'.$tag,
        ];

        foreach ($paths as $cachePath) {
            if (! is_dir($cachePath)) {
                continue;
            }

            foreach (scandir($cachePath) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..' || ! str_contains($entry, 'tag!')) {
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
    }

    public static function fallbackFromRedis(): void
    {
        config([
            'app.cache' => false,
            'cache.default' => 'array',
            'session.driver' => 'file',
        ]);

        // Drop any already-resolved Redis-backed managers so later code cannot
        // reuse a connection that failed AUTH / SELECT (NOAUTH).
        foreach (['cache', 'cache.store', 'session', 'session.store', 'redis'] as $abstract) {
            if (app()->bound($abstract)) {
                app()->forgetInstance($abstract);
            }
        }
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
            $connection = app('redis')->connection();
            $pong = $connection->ping();

            // Predis may return true, "+PONG", or a status object.
            if ($pong === true || $pong === '+PONG' || $pong === 'PONG') {
                return true;
            }

            return is_object($pong) && method_exists($pong, '__toString')
                && str_contains(strtoupper((string) $pong), 'PONG');
        } catch (Throwable $e) {
            // NOAUTH / wrong password / refused — treat as unreachable so checkout
            // can fall back instead of surfacing raw Predis errors to shoppers.
            if (self::isRedisAuthOrConnectivityFailure($e)) {
                return false;
            }

            return false;
        }
    }

    public static function isRedisAuthOrConnectivityFailure(Throwable $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'NOAUTH')
            || str_contains($message, 'Authentication required')
            || str_contains($message, 'invalid password')
            || str_contains($message, 'WRONGPASS')
            || str_contains($message, 'Connection refused')
            || str_contains($message, 'timed out')
            || str_contains($message, 'read error on connection');
    }
}
