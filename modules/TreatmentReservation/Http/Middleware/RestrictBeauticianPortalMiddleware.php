<?php

namespace Modules\TreatmentReservation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Beautician\Entities\Beautician;
use Modules\User\Entities\User;
use Symfony\Component\HttpFoundation\Response;

class RestrictBeauticianPortalMiddleware
{
    /**
     * @var array<int, string>
     */
    private array $allowedRoutePatterns = [
        'admin.treatment_reservations.portal',
        'admin.treatment_reservations.portal.*',
        'admin.beauticians.portal',
        'admin.beauticians.portal.*',
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
            if (! $routeName || ! fnmatch($pattern, $routeName)) {
                continue;
            }

            if (str_starts_with($pattern, 'admin.beauticians.portal')
                && ! $this->canAccessOwnBeauticianPortalRoute($request, $user)) {
                continue;
            }

            return $next($request);
        }

        return redirect()->to($user->adminHomeRoute());
    }


    private function canAccessOwnBeauticianPortalRoute(Request $request, User $user): bool
    {
        $beautician = Beautician::findForUser($user->id);
        $routeBeauticianId = (int) ($request->route('id') ?? $request->route('beautician')?->id ?? 0);

        return $beautician && (int) $beautician->id === $routeBeauticianId;
    }
}
