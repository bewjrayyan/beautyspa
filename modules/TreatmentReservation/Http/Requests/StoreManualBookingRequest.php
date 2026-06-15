<?php

namespace Modules\TreatmentReservation\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;
use Modules\TreatmentReservation\Http\Requests\Concerns\ValidatesManualBookingFields;
use Modules\TreatmentReservation\Rules\ValidBeauticianSlot;
use Modules\User\Support\PhoneNumber;

class StoreManualBookingRequest extends Request
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
        return $this->manualBookingFieldRules(requireReceipt: true);
    }


    /**
     * @return array<int, mixed>
     */
    public static function treatmentProductRule(): array
    {
        return [
            'required',
            'integer',
            Rule::exists('products', 'id')
                ->where('is_virtual', true)
                ->where('is_active', true)
                ->whereNull('deleted_at'),
        ];
    }
}
