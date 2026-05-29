<?php

namespace Modules\User\Http\Requests;

use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;
use Modules\Support\Rules\GoogleRecaptcha;

class RegisterRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'user::attributes.users';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', 'unique:users'],
            'phone' => ['required', new ValidPhone()],
            'password' => ['required', 'confirmed', 'min:6'],
            'privacy_policy' => ['accepted'],
            'referral_code' => ['nullable', 'string', 'max:16'],
            'g-recaptcha-response' => ['bail', 'sometimes', 'required', new GoogleRecaptcha()],
        ];
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
