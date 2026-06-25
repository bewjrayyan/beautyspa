<?php

namespace Modules\User\Http\Requests;

use Modules\Core\Http\Requests\Request;
use Modules\Support\Rules\GoogleRecaptcha;

class LoginRequest extends Request
{
    /**
     * Available attributes for users.
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
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        if (setting('google_recaptcha_enabled')) {
            $rules['g-recaptcha-response'] = ['bail', 'required', new GoogleRecaptcha('login')];
        }

        return $rules;
    }


    /**
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'g-recaptcha-response.required' => trans('support::recaptcha.validation.failed_to_verify'),
        ];
    }
}
