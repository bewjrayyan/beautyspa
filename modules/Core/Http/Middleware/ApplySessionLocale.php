<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class ApplySessionLocale
{
    /**
     * Apply locale from session on non-localized admin URLs.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale');

        if (is_string($locale) && in_array($locale, supported_locale_keys(), true)) {
            LaravelLocalization::setLocale($locale);
        } else {
            LaravelLocalization::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
