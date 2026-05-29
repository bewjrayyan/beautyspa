<?php

namespace Modules\Support\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('security.headers.enabled', true)) {
            return $response;
        }

        if ($this->shouldSkip($request, $response)) {
            return $response;
        }

        $isAdmin = $this->isAdminRequest($request);
        $allowSameOriginIframe = $this->allowsSameOriginIframe($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff', false);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        $response->headers->set(
            'X-Frame-Options',
            ($isAdmin && ! $allowSameOriginIframe) ? 'DENY' : 'SAMEORIGIN',
            false
        );
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(self)',
            false
        );

        if ($this->shouldSendHsts($request)) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
                false
            );
        }

        if (config('security.headers.csp_enabled', false)) {
            $headerName = config('security.headers.csp_report_only', true)
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

            $response->headers->set(
                $headerName,
                $this->buildContentSecurityPolicy($isAdmin, $allowSameOriginIframe),
                false
            );
        }

        return $response;
    }

    private function shouldSkip(Request $request, Response $response): bool
    {
        if ($request->is('install*') || ! config('app.installed')) {
            return true;
        }

        if (! $response->headers->has('Content-Type')) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type');

        return ! str_contains($contentType, 'text/html')
            && ! str_contains($contentType, 'application/xhtml');
    }

    private function isAdminRequest(Request $request): bool
    {
        if ($this->appInAdminPanel()) {
            return true;
        }

        $path = trim($request->path(), '/');

        return $path === 'admin' || str_starts_with($path, 'admin/');
    }

    private function appInAdminPanel(): bool
    {
        return (bool) (app()->bound('inAdminPanel') ? app('inAdminPanel') : false);
    }

    private function shouldSendHsts(Request $request): bool
    {
        if (! config('security.headers.hsts_enabled', true)) {
            return false;
        }

        if (! $request->isSecure()) {
            return false;
        }

        return app()->environment('production');
    }

    private function allowsSameOriginIframe(Request $request): bool
    {
        return $request->is('admin/file-manager*');
    }

    private function buildContentSecurityPolicy(bool $isAdmin, bool $allowSameOriginIframe = false): string
    {
        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "form-action 'self'",
        ];

        if ($isAdmin) {
            $directives[] = $allowSameOriginIframe
                ? "frame-ancestors 'self'"
                : "frame-ancestors 'none'";
            $directives[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:";
            $directives[] = "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com";
            $directives[] = "font-src 'self' data: https://fonts.gstatic.com";
            $directives[] = "img-src 'self' data: https: blob:";
            $directives[] = "connect-src 'self' https: wss:";
        } else {
            $directives[] = "frame-ancestors 'self'";
            $directives[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' https: blob:";
            $directives[] = "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com";
            $directives[] = "font-src 'self' data: https://fonts.gstatic.com";
            $directives[] = "img-src 'self' data: https: blob:";
            $directives[] = "connect-src 'self' https: wss:";
            $directives[] = "frame-src https:";
        }

        $reportUri = config('security.headers.csp_report_uri');

        if ($reportUri) {
            $directives[] = 'report-uri ' . $reportUri;
        }

        return implode('; ', $directives);
    }
}
