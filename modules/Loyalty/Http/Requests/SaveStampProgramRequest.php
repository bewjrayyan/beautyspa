<?php

namespace Modules\Loyalty\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Loyalty\Services\StampProgramEligibleProductService;

class SaveStampProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    protected function prepareForValidation(): void
    {
        $normalized = app(StampProgramEligibleProductService::class)->normalizeForStorage(
            $this->input('eligible_category_ids'),
            $this->input('eligible_product_ids'),
        );

        $this->merge(['product_ids' => $normalized]);
    }


    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'reward_description' => 'nullable|string|max:1000',
            'stamps_required' => 'required|integer|min:2|max:30',
            'validity_days' => 'required|integer|min:1|max:365',
            'virtual_treatments_only' => 'required|boolean',
            'eligible_category_ids' => 'nullable|array',
            'eligible_category_ids.*' => 'integer|min:1|exists:categories,id',
            'eligible_product_ids' => 'nullable|array',
            'eligible_product_ids.*' => 'integer|min:1|exists:products,id',
            'product_ids' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ];
    }
}
