<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\TreatmentReservation\Services\ManualBookingSlotsResolver;

class ManualBookingController extends Controller
{
    /**
     * Retained for TBA scheduling and rescheduling only; legacy booking CRUD
     * now lives exclusively in POS Booking.
     */
    public function slots(Request $request, ManualBookingSlotsResolver $slotsResolver): JsonResponse
    {
        $data = $request->validate([
            'beautician_id' => ['required', 'integer', Rule::exists('beauticians', 'id')->where('is_active', true)],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'booking_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where('is_virtual', true)->where('is_active', true)->whereNull('deleted_at')],
            'spa_branch_id' => ['nullable', 'integer', Rule::exists('spa_branches', 'id')->where('is_active', true)],
        ]);

        try {
            return response()->json(['slots' => $slotsResolver->resolve($data)]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
