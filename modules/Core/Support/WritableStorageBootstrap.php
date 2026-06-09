<?php

namespace Modules\Core\Support;

class WritableStorageBootstrap
{
    public static function isLocalEnvironment(): bool
    {
        return ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production') === 'local';
    }

    public static function apply(): void
    {
        foreach (static::storageDirectories() as $directory) {
            static::ensureDirectory($directory);
        }

        if (static::isLocalEnvironment()) {
            $logFile = static::logPath();

            if (! is_file($logFile)) {
                @touch($logFile);
            }

            @chmod($logFile, 0666);

            static::chmodRecursive(static::fileCachePath(), 0777);

            $sessionPath = static::sessionPath();

            if (is_dir($sessionPath)) {
                static::chmodRecursive($sessionPath, 0777);
            }

            $viewsPath = static::compiledViewsPath();

            if (is_dir($viewsPath)) {
                static::chmodRecursive($viewsPath, 0777);
            }
        }
    }

    /**
     * Resolve an env override only when the target directory is usable on this server.
     */
    public static function resolvePath(string $envKey, string $default): string
    {
        if (! static::isLocalEnvironment()) {
            return $default;
        }

        $override = $_ENV[$envKey] ?? getenv($envKey);

        if (is_string($override) && $override !== '' && static::isUsablePath($override)) {
            return $override;
        }

        return $default;
    }

    /**
     * @return list<string>
     */
    public static function storageDirectories(): array
    {
        return [
            storage_path('logs'),
            storage_path('framework/cache/local-data'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('framework'),
            storage_path('app'),
            storage_path('app/public'),
            storage_path('bootstrap/cache'),
        ];
    }

    public static function logPath(): string
    {
        return static::pathFromEnv('FLEETCART_LOG_PATH', storage_path('logs/laravel.log'));
    }

    public static function fileCachePath(): string
    {
        return static::pathFromEnv('FLEETCART_CACHE_PATH', storage_path('framework/cache/local-data'));
    }

    /**
     * @return list<string>
     */
    public static function localFileCacheRoots(): array
    {
        return array_values(array_unique([
            static::fileCachePath(),
            storage_path('framework/cache/local-data'),
            storage_path('framework/cache/data'),
        ]));
    }

    public static function unlinkTaggedCacheFile(string $table): void
    {
        $prefix = (string) config('cache.prefix', 'laravel_cache');
        $tagName = 'cache/tag!' . $prefix . '_' . $table;

        foreach (static::localFileCacheRoots() as $root) {
            $tagFile = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $tagName;

            if (is_file($tagFile)) {
                @unlink($tagFile);
            }
        }
    }

    public static function sessionPath(): string
    {
        return static::pathFromEnv('FLEETCART_SESSION_PATH', storage_path('framework/sessions'));
    }

    public static function compiledViewsPath(): string
    {
        return static::pathFromEnv('FLEETCART_VIEW_COMPILED_PATH', storage_path('framework/views'));
    }

    private static function pathFromEnv(string $key, string $default): string
    {
        return static::resolvePath($key, $default);
    }


    /**
     * Force Laravel config to project storage paths (ignores stale FLEETCART_* / config:cache).
     */
    public static function syncConfigPaths(): void
    {
        if (! function_exists('config')) {
            return;
        }

        config([
            'view.compiled' => static::compiledViewsPath(),
            'session.files' => static::sessionPath(),
            'cache.stores.file.path' => static::fileCachePath(),
            'logging.channels.single.path' => static::logPath(),
            'logging.channels.daily.path' => static::logPath(),
        ]);
    }


    private static function isUsablePath(string $path): bool
    {
        $directory = str_ends_with($path, '.log') ? dirname($path) : $path;

        if ($directory === '' || $directory === '.') {
            return false;
        }

        if (is_dir($directory)) {
            return is_writable($directory);
        }

        $parent = dirname($directory);

        return $parent !== $directory && is_dir($parent) && is_writable($parent);
    }

    private static function ensureDirectory(string $path): void
    {
        if (! is_dir($path)) {
            @mkdir($path, 0777, true);
        }

        @chmod($path, 0777);
    }

    private static function chmodRecursive(string $path, int $mode): void
    {
        if (! is_dir($path)) {
            return;
        }

        @chmod($path, $mode);

        $items = @scandir($path);

        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $item;

            if (is_dir($fullPath)) {
                static::chmodRecursive($fullPath, $mode);
            } else {
                @chmod($fullPath, $mode);
            }
        }
    }
}
