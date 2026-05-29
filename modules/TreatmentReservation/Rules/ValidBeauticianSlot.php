<?php

namespace Modules\TreatmentReservation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
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

        if (! app(BeauticianAvailabilityService::class)->isSlotAvailable($beauticianId, $date, (string) $value)) {
            $fail(trans('treatmentreservation::public.slot_unavailable'));
        }
    }
}
