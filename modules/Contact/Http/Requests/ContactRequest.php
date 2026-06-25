<?php

namespace Modules\Contact\Http\Requests;

use Modules\Core\Http\Requests\Request;
use Modules\Support\GoogleRecaptchaSettings;
use Modules\Support\Rules\GoogleRecaptcha;

class ContactRequest extends Request
{
    protected $availableAttributes = 'contact::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'email' => ['required', 'email'],
            'subject' => ['required'],
            'message' => ['required'],
        ];

        if (GoogleRecaptchaSettings::enabled()) {
            $rules['g-recaptcha-response'] = ['bail', 'required', new GoogleRecaptcha('contact')];
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
