<?php

namespace Modules\FlashSale\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Product\Entities\Product;

class SaveFlashSaleRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'flashsale::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'campaign_name' => ['required'],
            'products.*.product_id' => ['required', Rule::exists('products', 'id')],
            'products.*.end_date' => ['required', 'date'],
            'products.*.price' => ['required', 'numeric', 'min:0', 'max:99999999999999'],
            'products.*.qty' => ['required', 'integer', 'min:0'],
        ];
    }


    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $products = collect($this->input('products', []))
            ->map(function (array $product) {
                if (! isset($product['qty']) || $product['qty'] === '') {
                    $product['qty'] = 0;
                }

                return $product;
            })
            ->all();

        $this->merge(['products' => $products]);
    }


    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('products', []) as $index => $productData) {
                $product = Product::query()->find($productData['product_id'] ?? null);

                if (! $product) {
                    continue;
                }

                if (! $product->isVirtualTreatment()) {
                    $validator->errors()->add(
                        "products.{$index}.product_id",
                        trans('flashsale::flash_sales.validation.virtual_only')
                    );
                }
            }
        });
    }
}
