<?php

namespace AestheticCart\Http\Middleware;

use AestheticCart\Http\FixSubdirectoryRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Routing\Middleware\ValidateSignature;

/**
 * Validates signed relative URLs on subdirectory installs.
 *
 * Web requests sign paths like /fleetcart/secure/... while FixSubdirectoryRequest
 * strips the install base before validation sees /secure/... — accept both forms.
 */
class ValidateSubdirectoryRelativeSignature extends ValidateSignature
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Routing\Exceptions\InvalidSignatureException
     */
    public function handle($request, Closure $next, ...$args)
    {
        [$relative, $ignore] = $this->parseArguments($args);

        if ($request->hasValidSignatureWhileIgnoring($ignore, ! $relative)) {
            return $next($request);
        }

        if ($relative && $this->hasLegacyInstallBaseSignature($request, $ignore)) {
            return $next($request);
        }

        throw new InvalidSignatureException;
    }


    /**
     * @param  array<int, string>  $ignore
     */
    private function hasLegacyInstallBaseSignature(Request $request, array $ignore): bool
    {
        $basePath = FixSubdirectoryRequest::basePath();

        if ($basePath === '') {
            return false;
        }

        $legacyPath = rtrim($basePath, '/').'/'.ltrim($request->path(), '/');
        $queryString = $request->getQueryString();
        $legacyUri = $legacyPath.($queryString ? '?'.$queryString : '');

        $legacyRequest = Request::create($legacyUri, $request->method());

        return $legacyRequest->hasValidSignatureWhileIgnoring($ignore, false);
    }
}
