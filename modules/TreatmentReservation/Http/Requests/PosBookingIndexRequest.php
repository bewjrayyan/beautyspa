<?php

namespace Modules\TreatmentReservation\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class PosBookingIndexRequest extends Request
{
    public function authorize(): bool
    {
        return Gate::allows('viewAny', TreatmentBooking::class);
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', Rule::in(TreatmentBooking::statuses())],
            'beautician_id' => ['nullable', 'integer', 'exists:beauticians,id'],
            'customer_id' => ['nullable', 'integer', 'exists:users,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
