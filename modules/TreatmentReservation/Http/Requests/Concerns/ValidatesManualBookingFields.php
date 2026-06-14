<?php

namespace Modules\TreatmentReservation\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Http\Requests\StoreManualBookingRequest;

trait ValidatesManualBookingFields
{
    /**
     * @return array<string, mixed>
     */
    protected function manualBookingFieldRules(bool $requireReceipt = false): array
    {
        return [
            'customer_first_name' => ['required', 'string', 'max:255'],
            'customer_last_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', new \Modules\Core\Rules\ValidPhone()],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'product_id' => StoreManualBookingRequest::treatmentProductRule(),
            'beautician_id' => [
                'required',
                'integer',
                Rule::exists('beauticians', 'id')->where('is_active', true),
            ],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'string', 'max:20', new \Modules\TreatmentReservation\Rules\ValidBeauticianSlot()],
            'notes' => ['nullable', 'string', 'max:5000'],
            'payment_status' => ['required', 'string', Rule::in(TreatmentBooking::manualPaymentStatuses())],
            'payment_receipt' => [
                Rule::requiredIf(fn () => $requireReceipt && in_array(
                    $this->input('payment_status'),
                    TreatmentBooking::manualPaymentStatusesRequiringReceipt(),
                    true
                )),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf,webp',
                'max:10240',
            ],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable'],
            'variations' => ['nullable', 'array'],
            'variations.*' => ['nullable', 'string', 'max:255'],
            'variant_id' => ['nullable', 'integer'],
        ];
    }
}
