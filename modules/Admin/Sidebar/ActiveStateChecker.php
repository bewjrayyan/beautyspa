<?php

namespace Modules\Admin\Sidebar;

use Illuminate\Support\Facades\Request;
use Maatwebsite\Sidebar\Item;

class ActiveStateChecker
{
    public function isActive(Item $item): bool
    {
        foreach ($item->getItems() as $child) {
            if ($this->isActive($child)) {
                return true;
            }
        }

        // Parents with children may prefix-match (keep tree open on nested routes).
        // Leaf items must match exactly — otherwise Dashboard (/admin) activates on every /admin/* page.
        $allowPrefix = $item->hasItems();

        if ($path = $item->getActiveWhen()) {
            return $this->matches($path, $allowPrefix);
        }

        return $this->matches((string) $item->getUrl(), $allowPrefix);
    }

    protected function matches(string $pathOrUrl, bool $allowPrefix = false): bool
    {
        if ($pathOrUrl === '' || $pathOrUrl === '#') {
            return false;
        }

        [$pathPattern, $query] = $this->parsePathAndQuery($pathOrUrl);
        $pathPattern = $this->normalizePathPattern($pathPattern);

        if ($pathPattern === '') {
            return false;
        }

        // Query constraints (e.g. ?view=dashboard) also force an exact path match.
        $exactPath = ! $allowPrefix || $query !== [];

        if (! $this->pathMatches($pathPattern, $exactPath)) {
            return false;
        }

        return $this->queryMatches($query);
    }

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    protected function parsePathAndQuery(string $pathOrUrl): array
    {
        $pathOrUrl = trim($pathOrUrl);

        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            $parts = parse_url($pathOrUrl) ?: [];
            $path = (string) ($parts['path'] ?? '');
            $query = [];
            if (! empty($parts['query'])) {
                parse_str($parts['query'], $query);
            }

            return [$path, $this->stringifyQuery($query)];
        }

        $pathOrUrl = ltrim($pathOrUrl, '/');
        $query = [];

        if (str_contains($pathOrUrl, '?')) {
            [$path, $queryString] = explode('?', $pathOrUrl, 2);
            parse_str($queryString, $query);
        } else {
            $path = $pathOrUrl;
        }

        return [$path, $this->stringifyQuery($query)];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, string>
     */
    protected function stringifyQuery(array $query): array
    {
        $normalized = [];

        foreach ($query as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $normalized[(string) $key] = (string) $value;
        }

        return $normalized;
    }

    protected function normalizePathPattern(string $path): string
    {
        $path = trim($path, '/');
        $base = trim((string) (parse_url(url('/'), PHP_URL_PATH) ?: ''), '/');

        if ($base !== '' && str_starts_with($path, $base.'/')) {
            $path = substr($path, strlen($base) + 1);
        } elseif ($base !== '' && $path === $base) {
            $path = '';
        }

        return trim($path, '/');
    }

    protected function pathMatches(string $pathPattern, bool $exactOnly = false): bool
    {
        $requestPath = trim(Request::path(), '/');
        $base = trim((string) (parse_url(url('/'), PHP_URL_PATH) ?: ''), '/');
        $relativeRequest = $requestPath;

        if ($base !== '' && str_starts_with($requestPath, $base.'/')) {
            $relativeRequest = substr($requestPath, strlen($base) + 1);
        } elseif ($base !== '' && $requestPath === $base) {
            $relativeRequest = '';
        }

        if ($exactOnly) {
            return $relativeRequest === $pathPattern
                || Request::is($pathPattern);
        }

        $candidates = array_values(array_unique(array_filter([
            $pathPattern,
            $pathPattern.'/*',
            $relativeRequest === $pathPattern ? $pathPattern : null,
        ])));

        if (Request::is(...$candidates)) {
            return true;
        }

        return $relativeRequest === $pathPattern
            || str_starts_with($relativeRequest, $pathPattern.'/');
    }

    /**
     * @param  array<string, string>  $query
     */
    protected function queryMatches(array $query): bool
    {
        if ($query === []) {
            return true;
        }

        foreach ($query as $key => $expected) {
            $actual = Request::query($key);

            if ($actual === null || $actual === '') {
                // Reservation index defaults to dashboard when view is omitted.
                if ($key === 'view' && $expected === 'dashboard') {
                    continue;
                }

                return false;
            }

            if ((string) $actual !== (string) $expected) {
                return false;
            }
        }

        return true;
    }
}
