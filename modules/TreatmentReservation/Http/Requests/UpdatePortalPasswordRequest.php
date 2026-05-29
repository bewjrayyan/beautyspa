<?php

namespace Modules\TreatmentReservation\Http\Requests;

use Illuminate\Support\Facades\Hash;
use Modules\Core\Http\Requests\Request;

class UpdatePortalPasswordRequest extends Request
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! Hash::check((string) $value, (string) auth()->user()->password)) {
                        $fail(trans('validation.current_password'));
                    }
                },
            ],
            'password' => ['required', 'confirmed', 'min:6'],
        ];
    }


    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'current_password' => trans('treatmentreservation::admin.portal.current_password'),
            'password' => trans('treatmentreservation::admin.portal.new_password'),
            'password_confirmation' => trans('treatmentreservation::admin.portal.confirm_password'),
        ];
    }
}
