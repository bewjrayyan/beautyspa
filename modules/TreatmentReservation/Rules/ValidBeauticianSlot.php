<?php

namespace Modules\TreatmentReservation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityService;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;

class ValidBeauticianSlot implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $beauticianId = (int) request()->input('beautician_id');
        $date = request()->input('appointment_date');

        if (! $beauticianId || ! $date || ! $value) {
            return;
        }

        $excludeBookingId = request()->route('booking')?->id
            ?? (request()->filled('booking_id') ? request()->integer('booking_id') : null)
            ?? (request()->route('id') ? (int) request()->route('id') : null);

        $booking = $excludeBookingId
            ? TreatmentBooking::query()->find($excludeBookingId)
            : null;

        $productId = (int) (request()->input('product_id') ?: $booking?->product_id ?: 0);
        $spaBranchId = (int) (request()->input('spa_branch_id')
            ?: $booking?->spa_branch_id
            ?: $booking?->order?->spa_branch_id
            ?: 0);

        if ($productId && $spaBranchId && app('modules')->isEnabled('SpaBranch')) {
            $available = app(AppointmentAvailabilityService::class)->isSlotAvailable(
                $productId,
                $spaBranchId,
                $date,
                (string) $value,
                $beauticianId,
                $excludeBookingId ?: null
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
}
