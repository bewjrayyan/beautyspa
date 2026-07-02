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

        if ($user->hasAccess('admin.beauticians.edit')) {
            return $next($request);
        }

        if ($user->isBeauticianOnly()) {
            $beautician = Beautician::findForUser($user->id);
            $routeBeauticianId = (int) ($request->route('id') ?? $request->route('beautician')?->id ?? 0);

            if ($beautician && (int) $beautician->id === $routeBeauticianId) {
                return $next($request);
            }
        }

        abort(403);
    }
}
