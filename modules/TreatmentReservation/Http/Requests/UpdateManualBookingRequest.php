<?php

namespace Modules\TreatmentReservation\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;
use Modules\TreatmentReservation\Rules\ValidBeauticianSlot;
use Modules\User\Support\PhoneNumber;

class UpdateManualBookingRequest extends Request
{
    protected $availableAttributes = 'treatmentreservation::attributes.manual_booking';


    protected function prepareForValidation(): void
    {
        if ($this->filled('customer_phone')) {
            $this->merge([
                'customer_phone' => PhoneNumber::normalize($this->input('customer_phone')) ?: $this->input('customer_phone'),
            ]);
        }
    }


    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_first_name' => ['required', 'string', 'max:255'],
            'customer_last_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', new ValidPhone()],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'product_id' => StoreManualBookingRequest::treatmentProductRule(),
            'beautician_id' => [
                'required',
                'integer',
                Rule::exists('beauticians', 'id')->where('is_active', true),
            ],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'string', 'max:20', new ValidBeauticianSlot()],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
