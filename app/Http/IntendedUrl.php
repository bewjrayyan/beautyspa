<?php

namespace AestheticCart\Http;

use Modules\User\Entities\User;

class IntendedUrl
{
    /**
     * Normalize a full URL (strip legacy /public segment, apply APP_URL base path).
     */
    public static function normalize(string $url): string
    {
        $parts = parse_url($url);

        if (! isset($parts['path'])) {
            return $url;
        }

        $path = self::normalizePath($parts['path']);

        $normalized = ($parts['scheme'] ?? 'http') . '://' . ($parts['host'] ?? 'localhost') . $path;

        if (isset($parts['query']) && $parts['query'] !== '') {
            $normalized .= '?' . $parts['query'];
        }

        if (isset($parts['fragment']) && $parts['fragment'] !== '') {
            $normalized .= '#' . $parts['fragment'];
        }

        return aestheticcart_apply_install_base_url($normalized);
    }


    public static function normalizePath(string $path): string
    {
        $path = preg_replace('#/public(?=/|$)#', '', $path) ?: '/';

        if ($path === '') {
            $path = '/';
        }

        $basePath = FixSubdirectoryRequest::basePath();
        $baseSegment = $basePath !== '' ? preg_quote(ltrim($basePath, '/'), '#') . '/' : '';

        // Broken relative redirects (e.g. treatment-reservations/portal → treatment-reservations/my/job-sheet).
        $path = preg_replace(
            '#(/admin/treatment-reservations/)(?:' . $baseSegment . ')?my/#',
            '/admin/my/',
            $path
        ) ?? $path;

        if ($basePath !== '') {
            $escaped = preg_quote($basePath, '#');
            $path = preg_replace('#' . $escaped . '(?=' . $escaped . '/)#', '', $path) ?? $path;
        }

        return $path;
    }


    public static function pathWithoutInstallBase(string $path): string
    {
        $path = self::normalizePath($path);
        $basePath = FixSubdirectoryRequest::basePath();

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        return $path;
    }


    public static function isAdmin(string $url): bool
    {
        return str_starts_with(
            self::pathWithoutInstallBase(parse_url($url, PHP_URL_PATH) ?? '/'),
            '/admin'
        );
    }


    public static function isBeauticianPortalPath(string $path): bool
    {
        $path = self::pathWithoutInstallBase($path);

        return (bool) preg_match('#^/admin/my/(job-sheet|account|availability)(/|$)#', $path);
    }


    public static function isMalformedAdminPath(string $path): bool
    {
        $path = self::pathWithoutInstallBase($path);

        if (str_contains($path, '/treatment-reservations/my/')) {
            return true;
        }

        $basePath = FixSubdirectoryRequest::basePath();

        if ($basePath !== '' && str_contains($path, $basePath . '/')) {
            return true;
        }

        return false;
    }


    /**
     * Post-login redirect: dashboard for staff admins, portal for beautician-only, never malformed URLs.
     */
    public static function resolveAfterAdminLogin(mixed $intended, User $user): string
    {
        $default = $user->adminHomeRoute();

        if (! is_string($intended) || $intended === '' || ! self::isAdmin($intended)) {
            return $default;
        }

        $normalized = self::normalize($intended);
        $path = parse_url($normalized, PHP_URL_PATH) ?? '/';

        if (self::isMalformedAdminPath($path)) {
            return $default;
        }

        if (! $user->isBeauticianOnly() && self::isBeauticianPortalPath($path)) {
            return route('admin.dashboard.index');
        }

        return $normalized;
    }
}
