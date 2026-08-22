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
use Modules\TreatmentReservation\Services\CheckoutTreatmentScheduleHolds;

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
            'spa_branch_id' => ['nullable', 'integer', 'required_with:product_id'],
            'product_id' => ['nullable', 'integer'],
            'holds' => ['nullable', 'array', 'max:20'],
            'holds.*.beautician_id' => ['required_with:holds', 'integer'],
            'holds.*.appointment_date' => ['required_with:holds', 'date'],
            'holds.*.appointment_time' => ['required_with:holds', 'date_format:H:i'],
            'holds.*.product_id' => ['nullable', 'integer'],
            'holds.*.duration_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
        ]);

        $exists = Beautician::query()
            ->where('id', $beautician)
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            return response()->json(['message' => trans('treatmentreservation::public.booking_not_found')], 404);
        }

        $spaBranchId = (int) ($data['spa_branch_id'] ?? 0);
        $cartProductIds = $this->resolveCartTreatmentProductIds();
        $productId = (int) ($data['product_id'] ?? 0) ?: ($cartProductIds[0] ?? 0);

        // Checkout carts must always use product + branch schedules (never hourly beautician fallback).
        // Falling back caused the UI to offer times like 15:00 that fail server validation.
        if ($cartProductIds !== []) {
            if (! $productId) {
                throw ValidationException::withMessages(['product_id' => trans('validation.required')]);
            }

            if (! $spaBranchId) {
                throw ValidationException::withMessages(['spa_branch_id' => trans('validation.required')]);
            }

            if (! in_array($productId, $cartProductIds, true)) {
                throw ValidationException::withMessages(['product_id' => trans('validation.in')]);
            }
        }

        if ($productId || $spaBranchId) {
            if (! $productId) {
                throw ValidationException::withMessages(['product_id' => trans('validation.required')]);
            }

            if (! $spaBranchId) {
                throw ValidationException::withMessages(['spa_branch_id' => trans('validation.required')]);
            }

            if (! app('modules')->isEnabled('SpaBranch')) {
                throw ValidationException::withMessages(['spa_branch_id' => trans('validation.exists')]);
            }

            if (! SpaBranch::query()->whereKey($spaBranchId)->where('is_active', true)->exists()
                || ! Product::withoutGlobalScope('active')->whereKey($productId)->where('is_virtual', true)->where('is_active', true)->exists()
                || ! Beautician::query()->whereKey($beautician)->whereHas(
                    'spaBranches',
                    fn ($query) => $query->where('spa_branches.id', $spaBranchId)
                )->exists()) {
                throw ValidationException::withMessages(['spa_branch_id' => trans('validation.exists')]);
            }

            $holds = $data['holds'] ?? [];
            $slotOptions = $this->appointmentAvailability->slotOptions(
                $productId,
                $spaBranchId,
                $data['date'],
                $beautician
            );

            $duration = $this->appointmentAvailability->resolveDurationMinutes($productId, $spaBranchId);

            if ($holds !== []) {
                $slotOptions = CheckoutTreatmentScheduleHolds::applyToSlotOptions(
                    $slotOptions,
                    $beautician,
                    $data['date'],
                    $duration,
                    $holds
                );
            }

            $slots = array_values(array_map(
                static fn (array $option) => $option['time'],
                array_filter($slotOptions, static fn (array $option) => $option['status'] === 'available')
            ));

            return response()->json([
                'slots' => $slots,
                'slot_options' => $slotOptions,
                'duration_minutes' => $duration,
                'product_id' => $productId,
                'date' => $data['date'],
            ]);
        }

        $slotOptions = $this->beauticianAvailability->slotOptions($beautician, $data['date']);

        return response()->json([
            'slots' => array_values(array_map(
                static fn (array $option) => $option['time'],
                array_filter($slotOptions, static fn (array $option) => $option['status'] === 'available')
            )),
            'slot_options' => $slotOptions,
        ]);
    }


    /**
     * @param  list<string>  $slots
     * @param  list<array<string, mixed>>  $holds
     * @return list<string>
     */
    private function filterSlotsAgainstHolds(
        array $slots,
        int $beauticianId,
        string $date,
        int $durationMinutes,
        int $spaBranchId,
        array $holds,
    ): array {
        if ($slots === [] || $holds === []) {
            return $slots;
        }

        $windows = [];

        foreach ($holds as $hold) {
            if ((int) ($hold['beautician_id'] ?? 0) !== $beauticianId) {
                continue;
            }

            if ((string) ($hold['appointment_date'] ?? '') !== $date) {
                continue;
            }

            $start = $this->beauticianAvailability->normalizeTime((string) ($hold['appointment_time'] ?? ''));

            if ($start === null) {
                continue;
            }

            $holdDuration = (int) ($hold['duration_minutes'] ?? 0);

            if ($holdDuration < 1) {
                $holdProductId = (int) ($hold['product_id'] ?? 0);
                $holdDuration = $holdProductId
                    ? $this->appointmentAvailability->resolveDurationMinutes($holdProductId, $spaBranchId)
                    : $durationMinutes;
            }

            $startMin = $this->minutes($start);
            $windows[] = [$startMin, $startMin + max(1, $holdDuration)];
        }

        if ($windows === []) {
            return $slots;
        }

        return array_values(array_filter($slots, function (string $slot) use ($windows, $durationMinutes) {
            $startMin = $this->minutes($slot);
            $endMin = $startMin + max(1, $durationMinutes);

            foreach ($windows as [$otherStart, $otherEnd]) {
                if ($startMin < $otherEnd && $endMin > $otherStart) {
                    return false;
                }
            }

            return true;
        }));
    }


    private function minutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));

        return ($h * 60) + $m;
    }


    /**
     * @return list<int>
     */
    private function resolveCartTreatmentProductIds(): array
    {
        $ids = [];

        foreach (Cart::items() as $item) {
            $product = $item->product ?? null;

            if ($product && ($product->is_virtual ?? false)) {
                $ids[] = (int) $product->id;
            }
        }

        return array_values(array_unique($ids));
    }
}
