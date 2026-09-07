<?php

namespace Modules\TreatmentReservation\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;

class StorePosBookingRequest extends Request
{
    public function authorize(): bool
    {
        return Gate::allows('create', TreatmentBooking::class);
    }

    public function rules(): array
    {
        return [
            'request_key' => ['required', 'uuid'],
            'customer_id' => ['required', 'integer', 'exists:users,id'],
            'beautician_id' => ['required_without:items', 'integer', Rule::exists('beauticians', 'id')->where('is_active', true)],
            'spa_branch_id' => ['required_without:items', 'integer', Rule::exists('spa_branches', 'id')->where('is_active', true)],
            'product_id' => ['required_without:items', 'integer', Rule::exists('products', 'id')->where('is_virtual', true)->where('is_active', true)->whereNull('deleted_at')],
            'variant_id' => ['nullable', 'integer'],
            'options' => ['nullable', 'array', 'max:50'],
            'variations' => ['nullable', 'array', 'max:50'],
            'appointment_date' => ['nullable', 'date', 'after_or_equal:today'],
            'appointment_time' => ['nullable', 'string', 'max:20'],
            'schedule_later' => ['nullable', 'boolean'],
            'payment_status' => ['required', Rule::in([TreatmentBooking::PAYMENT_FULL_PAID])],
            'payment_receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'loyalty_points' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'items' => ['nullable', 'array', 'min:1', 'max:20'],
            'items.*.product_id' => ['required_with:items', 'integer', Rule::exists('products', 'id')->where('is_virtual', true)->where('is_active', true)->whereNull('deleted_at')],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.options' => ['nullable', 'array', 'max:50'],
            'items.*.variations' => ['nullable', 'array', 'max:50'],
            'items.*.beautician_id' => ['required_with:items', 'integer', Rule::exists('beauticians', 'id')->where('is_active', true)],
            'items.*.spa_branch_id' => ['required_with:items', 'integer', Rule::exists('spa_branches', 'id')->where('is_active', true)],
            'items.*.appointment_date' => ['nullable', 'date', 'after_or_equal:today'],
            'items.*.appointment_time' => ['nullable', 'string', 'max:20'],
            'items.*.schedule_later' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $customer = User::query()->whereKey($this->input('customer_id'))
                ->whereHas('roles', fn ($query) => $query->whereKey(setting('customer_role')))
                ->first();

            if (! $customer || ! $customer->isActivated()) {
                $validator->errors()->add('customer_id', 'The selected customer is not active.');
            }

            if (! $this->filled('items') && ! $this->boolean('schedule_later') && (! $this->filled('appointment_date') || ! $this->filled('appointment_time'))) {
                $validator->errors()->add('appointment_date', 'Appointment date and time are required unless scheduling later.');
            }

            foreach ((array) $this->input('items', []) as $index => $item) {
                if (! filter_var($item['schedule_later'] ?? false, FILTER_VALIDATE_BOOLEAN)
                    && (empty($item['appointment_date']) || empty($item['appointment_time']))) {
                    $validator->errors()->add("items.{$index}.appointment_date", 'Appointment date and time are required unless scheduling later.');
                }
            }
        });
    }
}
