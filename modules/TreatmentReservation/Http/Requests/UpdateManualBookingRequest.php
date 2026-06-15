<?php

namespace Modules\TreatmentReservation\Http\Requests;

use Modules\Core\Http\Requests\Request;
use Modules\TreatmentReservation\Http\Requests\Concerns\ValidatesManualBookingFields;
use Modules\User\Support\PhoneNumber;

class UpdateManualBookingRequest extends Request
{
    use ValidatesManualBookingFields;

    protected $availableAttributes = 'treatmentreservation::attributes.manual_booking';


    protected function prepareForValidation(): void
    {
        if ($this->filled('customer_phone')) {
            $this->merge([
                'customer_phone' => PhoneNumber::toE164($this->input('customer_phone')) ?: $this->input('customer_phone'),
            ]);
        }
    }


    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->manualBookingFieldRules(requireReceipt: false);
    }
}
