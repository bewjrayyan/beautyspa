<?php

namespace Modules\Loyalty\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveStampProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    protected function prepareForValidation(): void
    {
        if ($this->has('product_ids_text')) {
            $ids = array_values(array_filter(array_map(
                'intval',
                preg_split('/[\s,]+/', (string) $this->product_ids_text) ?: []
            )));

            $this->merge(['product_ids' => $ids ?: null]);
        }
    }


    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'reward_description' => 'nullable|string|max:1000',
            'stamps_required' => 'required|integer|min:2|max:30',
            'validity_days' => 'required|integer|min:1|max:365',
            'virtual_treatments_only' => 'required|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ];
    }
}
