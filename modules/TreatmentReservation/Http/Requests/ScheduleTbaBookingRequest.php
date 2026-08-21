<?php

namespace Modules\TreatmentReservation\Http\Requests;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\TreatmentReservation\Http\Requests\StoreManualBookingRequest;
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

        if ($booking && ! $this->filled('product_id') && $booking->product_id) {
            $this->merge(['product_id' => $booking->product_id]);
        }

        if ($booking && ! $this->filled('spa_branch_id')) {
            $branchId = $booking->spa_branch_id ?? $booking->order?->spa_branch_id;
            if ($branchId) {
                $this->merge(['spa_branch_id' => $branchId]);
            }
        }
    }


    public function rules(): array
    {
        return [
            'beautician_id' => [
                'required',
                'integer',
                Rule::exists('beauticians', 'id')->where('is_active', true),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $branchId = (int) $this->input('spa_branch_id');

                    if ($branchId && ! DB::table('beautician_spa_branch')
                        ->where('beautician_id', (int) $value)
                        ->where('spa_branch_id', $branchId)
                        ->exists()) {
                        $fail(trans('treatmentreservation::admin.manual_booking.beautician_branch_mismatch'));
                    }
                },
            ],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i', new ValidBeauticianSlot()],
            'spa_branch_id' => ['nullable', 'integer', Rule::exists('spa_branches', 'id')->where('is_active', true)],
            'product_id' => StoreManualBookingRequest::treatmentProductRule(),
            'notify_customer' => ['sometimes', 'boolean'],
        ];
    }
}
