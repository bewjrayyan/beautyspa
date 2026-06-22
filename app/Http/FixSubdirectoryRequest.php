<?php

namespace AestheticCart\Http;

class FixSubdirectoryRequest
{
    private static ?string $resolvedBasePath = null;

    private static ?string $resolvedAppUrl = null;

    /**
     * Strip the install subdirectory from REQUEST_URI (e.g. /v2/install → /install).
     */
    public static function apply(): void
    {
        $basePath = self::resolveBasePath();
        self::$resolvedBasePath = $basePath;

        if ($basePath === '') {
            return;
        }

        $resolvedUrl = self::buildAppUrl($basePath);

        if ($resolvedUrl !== '') {
            self::syncAppUrl($resolvedUrl);
            self::$resolvedAppUrl = $resolvedUrl;
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
        if (self::$resolvedBasePath !== null) {
            return self::$resolvedBasePath;
        }

        return self::resolveBasePath();
    }


    public static function resolvedAppUrl(): ?string
    {
        if (self::$resolvedAppUrl !== null) {
            return self::$resolvedAppUrl;
        }

        $appUrl = self::appUrl();

        return $appUrl !== '' ? $appUrl : null;
    }


    public static function appUrl(): string
    {
        if (self::$resolvedAppUrl !== null) {
            return self::$resolvedAppUrl;
        }

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


    private static function resolveBasePath(): string
    {
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $configured = self::configuredBasePath();

        if ($configured !== '' && self::pathStartsWithBase($requestPath, $configured)) {
            return $configured;
        }

        $fromScript = self::basePathFromScriptName();

        if ($fromScript !== '' && self::pathStartsWithBase($requestPath, $fromScript)) {
            return $fromScript;
        }

        $fromLocale = self::basePathFromLocaleSegment($requestPath);

        if ($fromLocale !== '') {
            return $fromLocale;
        }

        $fromRequest = self::basePathFromRequest($requestPath);

        if ($fromRequest !== '') {
            return $fromRequest;
        }

        if ($fromScript !== '') {
            return $fromScript;
        }

        return $configured;
    }


    private static function configuredBasePath(): string
    {
        $appUrl = self::appUrl();

        if ($appUrl === '') {
            return '';
        }

        $path = parse_url($appUrl, PHP_URL_PATH) ?? '';

        return rtrim($path, '/');
    }


    private static function basePathFromScriptName(): string
    {
        $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');

        if (preg_match('#^(/.+)/public/index\.php$#', $script, $matches)) {
            return $matches[1];
        }

        if (preg_match('#^(/.+)/index\.php$#', $script, $matches)) {
            return $matches[1];
        }

        return '';
    }


    private static function basePathFromRequest(string $path): string
    {
        foreach (['install', 'license', 'admin', 'api', 'blog'] as $segment) {
            if (preg_match('#^(.+)/'.preg_quote($segment, '#').'(?:/|$)#', $path, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }


    private static function basePathFromLocaleSegment(string $path): string
    {
        foreach (self::localeSegments() as $locale) {
            if (preg_match('#^(.+)/'.preg_quote($locale, '#').'(?:/|$)#', $path, $matches)) {
                $prefix = $matches[1];

                return $prefix === '/' ? '' : rtrim($prefix, '/');
            }
        }

        return '';
    }


    /**
     * @return array<int, string>
     */
    private static function localeSegments(): array
    {
        if (function_exists('supported_locale_keys')) {
            try {
                return supported_locale_keys();
            } catch (\Throwable) {
                // Helpers not ready during very early bootstrap.
            }
        }

        return ['en', 'ms'];
    }


    private static function pathStartsWithBase(string $path, string $basePath): bool
    {
        return $path === $basePath || str_starts_with($path, $basePath.'/');
    }


    private static function buildAppUrl(string $basePath): string
    {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');

        if ($host !== '') {
            $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
                ? 'https'
                : 'http';

            return $scheme.'://'.$host.$basePath;
        }

        $configured = self::appUrl();

        if ($configured !== '') {
            $configuredPath = rtrim(parse_url($configured, PHP_URL_PATH) ?? '', '/');

            if ($configuredPath === $basePath) {
                return $configured;
            }
        }

        return '';
    }


    private static function syncAppUrl(string $url): void
    {
        putenv('APP_URL='.$url);
        $_ENV['APP_URL'] = $url;
        $_SERVER['APP_URL'] = $url;

        if (function_exists('config')) {
            try {
                config(['app.url' => $url]);
            } catch (\Throwable) {
                // Config not bootstrapped yet.
            }
        }
    }
}
