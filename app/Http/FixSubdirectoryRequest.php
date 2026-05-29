<?php

namespace FleetCart\Http;

class FixSubdirectoryRequest
{
    /**
     * Strip the APP_URL path prefix from REQUEST_URI (e.g. /fleetcart).
     */
    public static function apply(): void
    {
        $basePath = self::basePath();

        if ($basePath === '') {
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $query = parse_url($requestUri, PHP_URL_QUERY);

        if (! str_starts_with($path, $basePath)) {
            return;
        }

        $path = substr($path, strlen($basePath)) ?: '/';

        $_SERVER['REQUEST_URI'] = $path.($query ? '?'.$query : '');

        $scriptName = $basePath.'/public/index.php';

        if (! str_contains((string) ($_SERVER['SCRIPT_NAME'] ?? ''), $basePath)) {
            $_SERVER['SCRIPT_NAME'] = $scriptName;
        }
    }


    public static function basePath(): string
    {
        $appUrl = self::appUrl();

        if ($appUrl === '') {
            return '';
        }

        $path = parse_url($appUrl, PHP_URL_PATH) ?? '';

        return rtrim($path, '/');
    }


    public static function appUrl(): string
    {
        if (function_exists('config')) {
            try {
                $configured = config('app.url');

                if (is_string($configured) && $configured !== '') {
                    return rtrim($configured, '/');
                }
            } catch (\Throwable) {
                // Config not bootstrapped yet (early index.php).
            }
        }

        $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '';

        return is_string($appUrl) ? rtrim($appUrl, '/') : '';
    }
}
