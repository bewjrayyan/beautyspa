<?php

namespace Modules\TreatmentReservation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Beautician\Entities\Beautician;
use Symfony\Component\HttpFoundation\Response;

class BeauticianPortalAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        // Admin users are authorized by the operation-specific permission
        // middleware attached to every preview route. This guard only keeps a
        // beautician account scoped to its own portal.
        if (! $user->isBeauticianOnly()) {
            return $next($request);
        }

        $beautician = Beautician::findForUser($user->id);
        $routeBeauticianId = (int) ($request->route('id') ?? $request->route('beautician')?->id ?? 0);

        if ($beautician && (int) $beautician->id === $routeBeauticianId) {
            return $next($request);
        }

        abort(403);
    }
}
