<?php

namespace Modules\TreatmentReservation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Beautician\Entities\Beautician;
use Symfony\Component\HttpFoundation\Response;

class BeauticianPortalMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $beautician = Beautician::findForUser(auth()->id());

        if (! $beautician) {
            abort(403, 'No beautician profile is linked to your account.');
        }

        $request->attributes->set('portal_beautician', $beautician);

        return $next($request);
    }
}
