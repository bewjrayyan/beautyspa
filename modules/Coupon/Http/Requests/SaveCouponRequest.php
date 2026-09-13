<?php

namespace Modules\Coupon\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;

class SaveCouponRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'coupon::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $couponId = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                Rule::unique('coupons', 'code')->ignore($couponId)->whereNull('deleted_at'),
            ],
            'is_percent' => 'required|boolean',
            'value' => 'required|numeric|min:0.0001|max:99999999999999',
            'free_shipping' => 'required|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'required|boolean',
            'minimum_spend' => 'nullable|numeric|min:0|max:99999999999999',
            'maximum_spend' => 'nullable|numeric|min:0|max:99999999999999',
            'usage_limit_per_coupon' => 'nullable|integer|min:1|max:4294967295',
            'usage_limit_per_customer' => 'nullable|integer|min:1|max:4294967295',
            'products' => 'nullable|array',
            'products.*' => 'integer|distinct|exists:products,id',
            'exclude_products' => 'nullable|array',
            'exclude_products.*' => 'integer|distinct|exists:products,id',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|distinct|exists:categories,id',
            'exclude_categories' => 'nullable|array',
            'exclude_categories.*' => 'integer|distinct|exists:categories,id',
        ];
    }


    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'code' => strtoupper(trim((string) $this->input('code'))),
        ]);
    }


    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->boolean('is_percent') && is_numeric($this->input('value')) && (float) $this->input('value') > 100) {
                $validator->errors()->add('value', trans('coupon::coupons.validation.percent_max'));
            }

            $startDate = strtotime((string) $this->input('start_date'));
            $endDate = strtotime((string) $this->input('end_date'));

            if ($startDate && $endDate && $endDate < $startDate) {
                $validator->errors()->add('end_date', trans('coupon::coupons.validation.end_date'));
            }

            $minimumSpend = $this->input('minimum_spend');
            $maximumSpend = $this->input('maximum_spend');

            if (is_numeric($minimumSpend) && is_numeric($maximumSpend) && (float) $maximumSpend < (float) $minimumSpend) {
                $validator->errors()->add('maximum_spend', trans('coupon::coupons.validation.maximum_spend'));
            }

            $couponLimit = $this->input('usage_limit_per_coupon');
            $customerLimit = $this->input('usage_limit_per_customer');

            if (is_numeric($couponLimit) && is_numeric($customerLimit) && (int) $customerLimit > (int) $couponLimit) {
                $validator->errors()->add('usage_limit_per_customer', trans('coupon::coupons.validation.customer_limit'));
            }

            $this->validateNoOverlap($validator, 'products', 'exclude_products');
            $this->validateNoOverlap($validator, 'categories', 'exclude_categories');
        });
    }


    public function messages()
    {
        return array_merge(parent::messages(), [
            'code.regex' => trans('coupon::coupons.validation.code_format'),
            'code.unique' => trans('coupon::coupons.validation.code_unique'),
        ]);
    }


    private function validateNoOverlap($validator, string $includedKey, string $excludedKey): void
    {
        $included = array_map('strval', (array) $this->input($includedKey, []));
        $excluded = array_map('strval', (array) $this->input($excludedKey, []));

        if (array_intersect($included, $excluded)) {
            $validator->errors()->add($excludedKey, trans('coupon::coupons.validation.scope_overlap'));
        }
    }
}
