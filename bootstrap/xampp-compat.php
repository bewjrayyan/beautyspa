<?php

/**
 * XAMPP/macOS: Apache often runs as "daemon" while CLI runs as your Mac user.
 * Runs before Laravel boots so logs, cache, and sessions are writable.
 */
(function (): void {
    $appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production';

    if ($appEnv !== 'local') {
        return;
    }

    $root = dirname(__DIR__);
    $storage = $root . '/storage';
    $tmp = sys_get_temp_dir();

    $setEnv = static function (string $key, string $value): void {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    };

    $ensureWritableDir = static function (string $path): bool {
        if (! is_dir($path)) {
            @mkdir($path, 0777, true);
        }

        @chmod($path, 0777);

        return is_dir($path);
    };

    $canWriteInDir = static function (string $dir): bool {
        if (! is_dir($dir)) {
            return false;
        }

        $testFile = $dir . '/.fleetcart-write-test-' . getmypid();

        $written = @file_put_contents($testFile, '1') !== false;

        if ($written) {
            @unlink($testFile);
        }

        return $written;
    };

    $canAppendLog = static function (string $logFile): bool {
        if (! is_file($logFile)) {
            @touch($logFile);
            @chmod($logFile, 0666);
        }

        $handle = @fopen($logFile, 'a');

        if ($handle === false) {
            return false;
        }

        fclose($handle);

        return true;
    };

    $resolveDir = static function (
        string $preferred,
        string $tmpPath,
        string $envKey
    ) use ($ensureWritableDir, $canWriteInDir, $setEnv, $tmp): string {
        $existing = $_ENV[$envKey] ?? getenv($envKey);

        if (is_string($existing) && $existing !== '') {
            if ($ensureWritableDir($existing) && $canWriteInDir($existing)) {
                $setEnv($envKey, $existing);

                return $existing;
            }
        }

        if ($ensureWritableDir($preferred) && $canWriteInDir($preferred)) {
            $setEnv($envKey, $preferred);

            return $preferred;
        }

        if ($ensureWritableDir($tmpPath) && $canWriteInDir($tmpPath)) {
            $setEnv($envKey, $tmpPath);

            return $tmpPath;
        }

        $setEnv($envKey, $tmp . '/fleetcart-fallback');

        return $tmp . '/fleetcart-fallback';
    };

    $resolveDir($storage . '/logs', $tmp . '/fleetcart-logs', 'FLEETCART_LOG_PATH');

    $logDir = $_ENV['FLEETCART_LOG_PATH'] ?? $storage . '/logs';
    $logFile = str_ends_with($logDir, '.log') ? $logDir : $logDir . '/laravel.log';

    if (! $canAppendLog($logFile)) {
        $fallbackDir = $tmp . '/fleetcart-logs';

        $ensureWritableDir($fallbackDir);
        $logFile = $fallbackDir . '/laravel.log';
        $canAppendLog($logFile);
        $setEnv('FLEETCART_LOG_PATH', $logFile);
    } else {
        @chmod($logFile, 0666);
        $setEnv('FLEETCART_LOG_PATH', $logFile);
    }

    $cacheDir = $resolveDir(
        $storage . '/framework/cache/local-data',
        $tmp . '/fleetcart-cache',
        'FLEETCART_CACHE_PATH'
    );

    $cachePoolDir = $cacheDir . '/cache';

    if (! is_dir($cachePoolDir)) {
        @mkdir($cachePoolDir, 0777, true);
    }

    @chmod($cachePoolDir, 0777);

    $resolveDir(
        $storage . '/framework/sessions',
        $tmp . '/fleetcart-sessions',
        'FLEETCART_SESSION_PATH'
    );

    $resolveDir(
        $storage . '/framework/views',
        $tmp . '/fleetcart-views',
        'FLEETCART_VIEW_COMPILED_PATH'
    );

    foreach ([
        $storage,
        $storage . '/framework',
        $storage . '/app',
        $storage . '/app/public',
        $storage . '/bootstrap/cache',
    ] as $dir) {
        $ensureWritableDir($dir);
    }
})();
