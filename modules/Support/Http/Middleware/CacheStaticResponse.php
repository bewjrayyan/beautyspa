<?php

namespace Modules\Support\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Page\Entities\Page;
use Symfony\Component\HttpFoundation\Response;

class CacheStaticResponse
{
    /**
     * Stand-in written to the cache in place of the CSRF token.
     *
     * The storefront layout embeds csrf_token() into the inline AestheticCart
     * bootstrap object, and axios sends it as X-CSRF-TOKEN on every request.
     * Storing that verbatim would hand one visitor's token to everybody else
     * for the whole TTL, so every guest POST would fail with a 419. We store a
     * placeholder instead and swap the current session's token back in on the
     * way out.
     */
    private const CSRF_PLACEHOLDER = '__RESPONSE_CACHE_CSRF_TOKEN__';


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
            return response($this->restoreCsrfToken($cached))
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('X-Response-Cache', 'HIT');
        }

        $response = $next($request);

        if ($this->shouldStore($response)) {
            Cache::put(
                $cacheKey,
                $this->maskCsrfToken((string) $response->getContent()),
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


    /**
     * Swap this request's CSRF token out before the HTML is cached.
     */
    private function maskCsrfToken(string $html): string
    {
        $token = csrf_token();

        if (! is_string($token) || $token === '') {
            return $html;
        }

        return str_replace($token, self::CSRF_PLACEHOLDER, $html);
    }


    /**
     * Put the current session's CSRF token back into cached HTML.
     */
    private function restoreCsrfToken(string $html): string
    {
        return str_replace(self::CSRF_PLACEHOLDER, (string) csrf_token(), $html);
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
