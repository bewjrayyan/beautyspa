<?php

namespace Modules\TreatmentReservation\Http\Requests;

use Modules\Core\Http\Requests\Request;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Rules\ValidBeauticianSlot;

class RescheduleTreatmentBookingRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $routeBooking = $this->route('booking');
        $bookingId = $routeBooking instanceof TreatmentBooking
            ? $routeBooking->id
            : ($routeBooking ?: $this->route('id'));
        $booking = $bookingId
            ? TreatmentBooking::query()->with('order')->find($bookingId)
            : null;

        if (! $booking) {
            return;
        }

        $this->merge([
            'booking_id' => $booking->id,
            'beautician_id' => $booking->beautician_id,
            'product_id' => $booking->product_id,
            'spa_branch_id' => $booking->spa_branch_id ?? $booking->order?->spa_branch_id,
        ]);
    }


    public function rules(): array
    {
        return [
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i', new ValidBeauticianSlot()],
            'notify_customer' => ['sometimes', 'boolean'],
            'notify_beautician' => ['sometimes', 'boolean'],
            'booking_id' => ['required', 'integer'],
            'beautician_id' => ['required', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'spa_branch_id' => ['nullable', 'integer'],
        ];
    }
}
