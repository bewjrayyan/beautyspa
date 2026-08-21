<?php

namespace Modules\TreatmentReservation\Http\Requests;

use Modules\Core\Http\Requests\Request;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Rules\ValidBeauticianSlot;

class ScheduleTbaBookingRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $booking = $this->route('id')
            ? TreatmentBooking::query()->find($this->route('id'))
            : null;

        if ($booking && ! $this->filled('beautician_id')) {
            $this->merge(['beautician_id' => $booking->beautician_id]);
        }

        if ($booking && ! $this->filled('booking_id')) {
            $this->merge(['booking_id' => $booking->id]);
        }
    }


    public function rules(): array
    {
        return [
            'beautician_id' => ['required', 'integer', 'exists:beauticians,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i', new ValidBeauticianSlot()],
            'notify_customer' => ['sometimes', 'boolean'],
        ];
    }
}
