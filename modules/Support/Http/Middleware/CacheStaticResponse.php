<?php

namespace Modules\Support\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Page\Entities\Page;
use Symfony\Component\HttpFoundation\Response;

class CacheStaticResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('performance.response_cache.enabled', false)) {
            return $next($request);
        }

        if ($request->user() || $request->method() !== 'GET') {
            return $next($request);
        }

        if ($request->routeIs('home') && config('performance.response_cache.home_enabled', false)) {
            return $this->respondFromCache($request, $next, 'home:' . locale());
        }

        $slug = $request->route('slug');

        if (! is_string($slug) || ! $this->isCacheableSlug($slug)) {
            return $next($request);
        }

        return $this->respondFromCache($request, $next, 'page:' . locale() . ':' . $slug);
    }


    private function respondFromCache(Request $request, Closure $next, string $cacheSuffix): Response
    {
        $version = (int) Cache::get('page_response_version', 1);
        $cacheKey = 'page_response:' . $version . ':' . $cacheSuffix;

        $cached = Cache::get($cacheKey);

        if (is_string($cached) && $cached !== '') {
            return response($cached)
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('X-Response-Cache', 'HIT');
        }

        $response = $next($request);

        if ($this->shouldStore($response)) {
            Cache::put(
                $cacheKey,
                $response->getContent(),
                now()->addMinutes((int) config('performance.response_cache.ttl_minutes', 60))
            );
        }

        $response->headers->set('X-Response-Cache', 'MISS');

        return $response;
    }


    private function isCacheableSlug(string $slug): bool
    {
        $always = config('performance.response_cache.slugs', [
            'faq',
            'terms-conditions',
            'privacy-policy',
        ]);

        if (in_array($slug, $always, true)) {
            return true;
        }

        return Page::withoutGlobalScope('active')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->exists();
    }


    private function shouldStore(Response $response): bool
    {
        if (! $response->isSuccessful()) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type');

        return str_contains($contentType, 'text/html');
    }
}
