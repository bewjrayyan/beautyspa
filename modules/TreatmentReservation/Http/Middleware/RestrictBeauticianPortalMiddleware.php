<?php

namespace Modules\TreatmentReservation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictBeauticianPortalMiddleware
{
    /**
     * @var array<int, string>
     */
    private array $allowedRoutePatterns = [
        'admin.treatment_reservations.portal',
        'admin.treatment_reservations.portal.*',
        'admin.logout',
        'admin.profile.*',
    ];


    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->isBeauticianOnly()) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();

        foreach ($this->allowedRoutePatterns as $pattern) {
            if ($routeName && fnmatch($pattern, $routeName)) {
                return $next($request);
            }
        }

        return redirect()->route('admin.treatment_reservations.portal');
    }
}
