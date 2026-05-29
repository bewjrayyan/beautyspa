<?php

namespace Modules\TreatmentReservation\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;
use Modules\User\Support\PhoneNumber;

class UpdatePortalProfileRequest extends Request
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => PhoneNumber::normalize($this->input('phone')) ?: $this->input('phone'),
            ]);
        }
    }


    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'required',
                new ValidPhone(),
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'job_title' => ['nullable', 'string', 'max:255'],
        ];
    }


    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => trans('user::attributes.users.first_name'),
            'last_name' => trans('user::attributes.users.last_name'),
            'email' => trans('treatmentreservation::admin.portal.login_email'),
            'phone' => trans('beautician::attributes.phone'),
            'date_of_birth' => trans('treatmentreservation::admin.portal.date_of_birth'),
            'job_title' => trans('beautician::attributes.job_title'),
        ];
    }
}
