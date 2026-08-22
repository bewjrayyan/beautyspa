<?php

namespace Modules\TreatmentReservation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityService;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;
use Modules\TreatmentReservation\Services\CheckoutTreatmentScheduleHolds;

class ValidBeauticianSlot implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        [$beauticianId, $date, $productId, $spaBranchId] = $this->contextFromAttribute($attribute);

        if (! $beauticianId || ! $date || ! $value) {
            return;
        }

        $excludeBookingId = request()->route('booking')?->id
            ?? (request()->filled('booking_id') ? request()->integer('booking_id') : null)
            ?? (request()->route('id') ? (int) request()->route('id') : null);

        $booking = $excludeBookingId
            ? TreatmentBooking::query()->find($excludeBookingId)
            : null;

        $productId = $productId
            ?: (int) (request()->input('product_id') ?: $booking?->product_id ?: 0);
        $spaBranchId = $spaBranchId
            ?: (int) (request()->input('spa_branch_id')
                ?: $booking?->spa_branch_id
                ?: $booking?->order?->spa_branch_id
                ?: 0);

        $lineIndex = null;
        if (preg_match('/^treatment_bookings\.(\d+)\./', $attribute, $lineMatch)) {
            $lineIndex = (int) $lineMatch[1];
        }

        $holds = CheckoutTreatmentScheduleHolds::fromRequest(request(), $lineIndex);

        if ($productId && $spaBranchId && app('modules')->isEnabled('SpaBranch')) {
            $availability = app(AppointmentAvailabilityService::class);
            $normalized = $availability->normalizeTime((string) $value);
            $duration = $availability->resolveDurationMinutes($productId, $spaBranchId);

            if ($normalized === null || ! $availability->isTimeInSchedule($productId, $spaBranchId, $date, $normalized, $beauticianId)) {
                $fail(trans('treatmentreservation::public.slot_not_in_schedule'));

                return;
            }

            if ($holds !== [] && CheckoutTreatmentScheduleHolds::slotConflictsWithHolds(
                $beauticianId,
                $date,
                $normalized,
                $duration,
                $holds
            )) {
                $fail(trans('checkout::messages.treatment_schedule_overlap'));

                return;
            }

            if ($availability->isBeauticianBlockedForSlot($beauticianId, $date, $normalized, $duration)) {
                $fail(trans('treatmentreservation::public.slot_beautician_unavailable'));

                return;
            }

            $available = $availability->isSlotAvailable(
                $productId,
                $spaBranchId,
                $date,
                $normalized,
                $beauticianId,
                $excludeBookingId ?: null,
                null,
                $holds
            );
        } else {
            $available = app(BeauticianAvailabilityService::class)->isSlotAvailable(
                $beauticianId,
                $date,
                (string) $value,
                $excludeBookingId ?: null
            );
        }

        if (! $available) {
            $fail(trans('treatmentreservation::public.slot_unavailable'));
        }
    }

    /**
     * @return array{0: int, 1: ?string, 2: int, 3: int}
     */
    private function contextFromAttribute(string $attribute): array
    {
        if (preg_match('/^treatment_bookings\.(\d+)\.appointment_time$/', $attribute, $matches)) {
            $prefix = "treatment_bookings.{$matches[1]}";

            return [
                (int) request()->input("{$prefix}.beautician_id"),
                request()->input("{$prefix}.appointment_date"),
                (int) request()->input("{$prefix}.product_id"),
                (int) request()->input('spa_branch_id'),
            ];
        }

        return [
            (int) request()->input('beautician_id'),
            request()->input('appointment_date'),
            (int) request()->input('product_id'),
            (int) request()->input('spa_branch_id'),
        ];
    }
}
