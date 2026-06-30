<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Modules\Option\Entities\Option;
use Modules\Product\Entities\Product;
use Modules\Core\Http\Requests\Request;
use Modules\Variation\Entities\Variation;
use Modules\Product\Rules\DistinctProductVariationValueLabel;

class SaveProductRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'product::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            $this->getProductRules(),
            $this->getProductAttributeRules(),
            $this->getProductVariationsRules(),
            $this->getProductVariantsRules(),
            $this->getProductOptionsRules(),
        );
    }


    protected function prepareForValidation(): void
    {
        if ($this->has('description')) {
            $this->merge(['description' => clean_html($this->input('description'))]);
        }

        if ($this->has('short_description')) {
            $this->merge(['short_description' => clean_html($this->input('short_description'))]);
        }

        if (! $this->has('price')) {
            return;
        }

        if ($this->input('price') === null || $this->input('price') === '') {
            $this->merge(['price' => 0]);
        }

        if ($this->has('loyalty_bonus_points') && $this->input('loyalty_bonus_points') === '') {
            $this->merge(['loyalty_bonus_points' => 0]);
        }

        if ($this->has('loyalty_earn_multiplier') && $this->input('loyalty_earn_multiplier') === '') {
            $this->merge(['loyalty_earn_multiplier' => 1]);
        }

        $this->normalizeVariantScheduleDates();
    }


    private function normalizeVariantScheduleDates(): void
    {
        if (! $this->has('variants') || ! is_array($this->input('variants'))) {
            return;
        }

        $variants = $this->input('variants');

        foreach ($variants as $uid => $variant) {
            if (! is_array($variant)) {
                continue;
            }

            foreach (['special_price_start', 'special_price_end'] as $key) {
                if (empty($variant[$key])) {
                    continue;
                }

                $variants[$uid][$key] = $this->normalizeScheduleDateValue($variant[$key]);
            }
        }

        $this->merge(['variants' => $variants]);
    }


    private function normalizeScheduleDateValue(string $value): string
    {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', $value)) {
            return Carbon::createFromFormat('d/m/Y H:i', $value)->format('Y-m-d H:i:s');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            return $value . ':00';
        }

        return $value;
    }


    public function getProductRules(): array
    {
        return array_merge(
            [
                'slug' => $this->getSlugRules(),
                'name' => 'required',
                'description' => 'required',
                'brand_id' => ['nullable', Rule::exists('brands', 'id')],
                'tax_class_id' => ['nullable', Rule::exists('tax_classes', 'id')],
                'price' => [
                    Rule::requiredIf(fn () => ! $this->hasFilledVariants()),
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999999999',
                ],
                'special_price' => 'nullable|numeric|min:0|max:99999999999999',
                'special_price_type' => ['nullable', Rule::in(['fixed', 'percent'])],
                'special_price_start' => 'nullable|date|before:special_price_end',
                'special_price_end' => 'nullable|date|after:special_price_start',
                'manage_stock' => 'required|boolean',
                'qty' => 'required_if:manage_stock,1|nullable|numeric',
                'in_stock' => 'required|boolean',
                'new_from' => 'nullable|date',
                'new_to' => 'nullable|date',
                'is_virtual' => 'required|boolean',
                'treatment_category_id' => 'nullable|integer|exists:treatment_categories,id',
                'is_active' => 'required|boolean',
                'loyalty_bonus_points' => 'nullable|integer|min:0|max:9999999',
                'loyalty_earn_multiplier' => 'nullable|numeric|min:0|max:100',
            ],
            $this->getInventoryRules()
        );
    }


    public function getInventoryRules(): array
    {
        if (!$this->request->has('variations')) {
            return [
                'manage_stock' => 'required|boolean',
                'qty' => 'required_if:manage_stock,1|nullable|numeric',
                'in_stock' => 'required|boolean',
            ];
        }

        return [];
    }


    public function getProductAttributeRules(): array
    {
        return [
            'attributes.*.attribute_id' => ['required_with:attributes.*.values', Rule::exists('attributes', 'id')],
            'attributes.*.values' => ['required_with:attributes.*.attribute_id', Rule::exists('attribute_values', 'id')],
        ];
    }


    public function getProductVariationsRules(): array
    {
        return [
            'variations.*.name' => 'required_with:variations.*.type',
            'variations.*.type' => ['nullable', 'required_with:variations.*.name', Rule::in(Variation::TYPES)],
            'variations.*.values.*.label' => ['required_with:variations.*.type', new DistinctProductVariationValueLabel()],
            'variations.*.values.*.color' => ['required_if:variations.*.type,color', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'variations.*.values.*.image' => 'required_if:type,image|integer|min:1',
        ];
    }


    public function getProductVariantsRules(): array
    {
        return [
            'variants.*.name' => 'required',
            'variants.*.sku' => 'nullable',
            'variants.*.price' => 'required_if:variants.*.is_active,true|nullable|numeric|min:0|max:99999999999999',
            'variants.*.special_price' => 'nullable|numeric|min:0|max:99999999999999',
            'variants.*.special_price_type' => ['nullable', Rule::in(['fixed', 'percent'])],
            'variants.*.special_price_start' => 'nullable|date|before:variants.*.special_price_end',
            'variants.*.special_price_end' => 'nullable|date|after:variants.*.special_price_start',
            'variants.*.manage_stock' => 'required_if:variants.*.is_active,1|boolean',
            'variants.*.qty' => 'required_if:variants.*.is_active,1|required_if:variants.*.manage_stock,1|nullable|numeric',
            'variants.*.in_stock' => 'required_if:variants.*.is_active,1|boolean',
            'variants.*.is_active' => 'required|boolean',
        ];
    }


    public function getProductOptionsRules(): array
    {
        return [
            'options.*.name' => 'required_with:options.*.type',
            'options.*.type' => ['nullable', 'required_with:options.*.name', Rule::in(Option::TYPES)],
            'options.*.is_required' => ['required_with:options.*.name', 'boolean'],
            'options.*.values.*.label' => 'required_if:options.*.type,dropdown,checkbox,checkbox_custom,radio,radio_custom,multiple_select',
            'options.*.values.*.price' => 'nullable|numeric|min:0|max:99999999999999',
            'options.*.values.*.price_type' => ['required', Rule::in(['fixed', 'percent'])],
        ];
    }


    public function messages()
    {
        return array_merge(parent::messages(), [
            'price.required_without' => trans('product::validation.price_field_is_required'),
        ]);
    }


    private function getSlugRules(): array
    {
        $rules = $this->route()->getName() === 'admin.products.update' ? ['required'] : ['sometimes'];

        $slug = Product::withoutGlobalScope('active')
            ->where('id', $this->id)
            ->value('slug');

        $rules[] = Rule::unique('products', 'slug')->ignore($slug, 'slug');

        return $rules;
    }


    private function hasFilledVariants(): bool
    {
        $variants = $this->input('variants');

        return is_array($variants) && count($variants) > 0;
    }
}
