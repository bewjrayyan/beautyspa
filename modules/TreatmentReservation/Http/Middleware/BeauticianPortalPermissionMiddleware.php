<?php

namespace Modules\TreatmentReservation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Beautician\Entities\Beautician;
use Symfony\Component\HttpFoundation\Response;

class BeauticianPortalPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        $beautician = $request->attributes->get('portal_beautician');

        if (! $user || ! $beautician instanceof Beautician) {
            abort(403);
        }

        if ((int) $beautician->user_id === (int) $user->id) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if ($permission !== '' && $user->hasAccess($permission)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
