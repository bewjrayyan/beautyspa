<?php

namespace Modules\Review\Http\Requests;

use Modules\Core\Http\Requests\Request;
use Modules\Support\GoogleRecaptchaSettings;
use Modules\Support\Rules\GoogleRecaptcha;

class StoreReviewRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'review::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'rating' => 'required|numeric|min:1|max:5',
            'reviewer_name' => 'required',
            'comment' => 'required',
        ];

        if (GoogleRecaptchaSettings::enabled()) {
            $rules['g-recaptcha-response'] = [
                'bail',
                'required',
                new GoogleRecaptcha('review'),
            ];
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
