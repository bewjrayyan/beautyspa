<?php

namespace Modules\TreatmentReservation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Services\AdminPortalPreview;
use Symfony\Component\HttpFoundation\Response;

class PortalBeauticianFromRouteMiddleware
{
    public function __construct(
        private AdminPortalPreview $portalPreview,
    ) {}


    public function handle(Request $request, Closure $next): Response
    {
        $beautician = $request->route('beautician');

        if (! $beautician instanceof Beautician) {
            $id = $request->route('id') ?? $beautician;

            $beautician = Beautician::query()->findOrFail($id);
        }

        $request->attributes->set('portal_beautician', $beautician);
        $this->portalPreview->activate($beautician);

        return $next($request);
    }
}
