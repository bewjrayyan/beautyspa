<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Services\ManualBookingSlotsResolver;

class PortalManualBookingController extends ManualBookingController
{
    public function slots(Request $request, ManualBookingSlotsResolver $slotsResolver): JsonResponse
    {
        $beautician = Beautician::findForUser((int) $request->user()?->id);

        abort_unless($beautician && (int) $beautician->id === $request->integer('beautician_id'), 403);

        return parent::slots($request, $slotsResolver);
    }
}
