<?php

namespace Modules\TreatmentReservation\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;

class UpdatePosBookingRequest extends Request
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('booking'));
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'integer', 'exists:users,id'],
            'beautician_id' => ['sometimes', 'integer', Rule::exists('beauticians', 'id')->where('is_active', true)],
            'spa_branch_id' => ['sometimes', 'integer', Rule::exists('spa_branches', 'id')->where('is_active', true)],
            'product_id' => ['sometimes', 'integer', Rule::exists('products', 'id')->where('is_virtual', true)->where('is_active', true)->whereNull('deleted_at')],
            'appointment_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:today'],
            'appointment_time' => ['sometimes', 'nullable', 'string', 'max:20'],
            'schedule_later' => ['sometimes', 'boolean'],
            'payment_status' => ['sometimes', Rule::in([TreatmentBooking::PAYMENT_FULL_PAID])],
            'payment_receipt' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::in(TreatmentBooking::statuses())],
            'options' => ['sometimes', 'array', 'max:50'],
            'variations' => ['sometimes', 'array', 'max:50'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('customer_id')) {
                return;
            }

            $customer = User::query()->whereKey($this->input('customer_id'))
                ->whereHas('roles', fn ($query) => $query->whereKey(setting('customer_role')))
                ->first();

            if (! $customer || ! $customer->isActivated()) {
                $validator->errors()->add('customer_id', 'The selected customer is not active.');
            }
        });
    }
}
