<?php

namespace Modules\TreatmentReservation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Beautician\Entities\Beautician;
use Modules\Cart\Facades\Cart;
use Modules\Product\Entities\Product;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityService;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;

class AvailabilitySlotsController extends Controller
{
    public function __construct(
        private AppointmentAvailabilityService $appointmentAvailability,
        private BeauticianAvailabilityService $beauticianAvailability,
    ) {}


    public function __invoke(Request $request, int $beautician): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'spa_branch_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
        ]);

        $exists = Beautician::query()
            ->where('id', $beautician)
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            return response()->json(['message' => trans('treatmentreservation::public.booking_not_found')], 404);
        }

        $spaBranchId = (int) ($data['spa_branch_id'] ?? 0);
        $cartProductId = $this->resolveCartTreatmentProductId();
        $productId = (int) ($data['product_id'] ?? 0) ?: $cartProductId;

        if ($cartProductId && $productId !== $cartProductId) {
            throw ValidationException::withMessages(['product_id' => trans('validation.in')]);
        }

        if ($productId && $spaBranchId && app('modules')->isEnabled('SpaBranch')) {
            if (! SpaBranch::query()->whereKey($spaBranchId)->where('is_active', true)->exists()
                || ! Product::withoutGlobalScope('active')->whereKey($productId)->where('is_virtual', true)->where('is_active', true)->exists()
                || ! Beautician::query()->whereKey($beautician)->whereHas(
                    'spaBranches',
                    fn ($query) => $query->where('spa_branches.id', $spaBranchId)
                )->exists()) {
                throw ValidationException::withMessages(['spa_branch_id' => trans('validation.exists')]);
            }

            return response()->json([
                'slots' => $this->appointmentAvailability->availableSlots(
                    $productId,
                    $spaBranchId,
                    $data['date'],
                    $beautician
                ),
            ]);
        }

        return response()->json([
            'slots' => $this->beauticianAvailability->availableSlots($beautician, $data['date']),
        ]);
    }


    private function resolveCartTreatmentProductId(): ?int
    {
        foreach (Cart::items() as $item) {
            $product = $item->product ?? null;

            if ($product && ($product->is_virtual ?? false)) {
                return (int) $product->id;
            }
        }

        return null;
    }
}
