<?php

namespace Modules\TreatmentReservation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;

class AvailabilitySlotsController extends Controller
{
    public function __construct(
        private BeauticianAvailabilityService $availability
    ) {}


    public function __invoke(Request $request, int $beautician): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $exists = Beautician::query()
            ->where('id', $beautician)
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            return response()->json(['message' => trans('treatmentreservation::public.booking_not_found')], 404);
        }

        return response()->json([
            'slots' => $this->availability->availableSlots($beautician, $request->input('date')),
        ]);
    }
}
