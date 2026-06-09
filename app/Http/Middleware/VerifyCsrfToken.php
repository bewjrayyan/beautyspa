<?php

namespace AestheticCart\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Illuminate\Http\Request;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];


    protected function inExceptArray($request): bool
    {
        if (! config('app.installed') && $this->isInstallRequest($request)) {
            return true;
        }

        return parent::inExceptArray($request);
    }


    private function isInstallRequest(Request $request): bool
    {
        return $request->is('install') || $request->is('install/*');
    }
}
