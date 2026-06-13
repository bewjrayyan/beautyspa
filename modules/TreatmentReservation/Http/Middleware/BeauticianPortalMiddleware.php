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
        $beautician = $request->attributes->get('portal_beautician')
            ?? Beautician::findForUser(auth()->id());

        if (! $beautician) {
            $user = auth()->user();

            if ($user?->hasAccess('admin.treatment_reservations.index')) {
                return redirect()->route('admin.treatment_reservations.index', [
                    'view' => 'kanban',
                ]);
            }

            abort(403, trans('treatmentreservation::admin.portal.no_beautician_profile'));
        }

        $request->attributes->set('portal_beautician', $beautician);

        return $next($request);
    }
}
