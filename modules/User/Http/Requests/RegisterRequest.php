<?php

namespace Modules\User\Http\Requests;

use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;
use Modules\Support\GoogleRecaptchaSettings;
use Modules\Support\Rules\GoogleRecaptcha;
use Modules\User\Support\PhoneNumber;

class RegisterRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'user::attributes.users';


    protected function prepareForValidation(): void
    {
        if (! $this->has('phone')) {
            return;
        }

        $phone = trim((string) $this->input('phone'));

        if ($phone === '') {
            return;
        }

        if (str_starts_with($phone, '+')) {
            $this->merge(['phone' => PhoneNumber::toE164($phone)]);

            return;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        // Malaysian mobile without leading 0 (e.g. 17-257 9723 from intl-tel-input display).
        if (preg_match('/^1\d{8,9}$/', $digits)) {
            $this->merge(['phone' => '+60' . $digits]);

            return;
        }

        $this->merge(['phone' => PhoneNumber::toE164($phone)]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', 'unique:users'],
            'phone' => ['required', new ValidPhone()],
            'password' => ['required', 'confirmed', 'min:6'],
            'privacy_policy' => ['accepted'],
            'referral_code' => ['nullable', 'string', 'max:16'],
        ];

        if (GoogleRecaptchaSettings::enabled()) {
            $rules['g-recaptcha-response'] = ['bail', 'required', new GoogleRecaptcha('register')];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return array_merge(parent::messages(), [
            'g-recaptcha-response.required' => trans('support::recaptcha.validation.failed_to_verify'),
        ]);
    }
}
