<?php

namespace Modules\Loyalty\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    protected function prepareForValidation(): void
    {
        if ($this->has('benefits_text')) {
            $lines = array_values(array_filter(array_map(
                'trim',
                preg_split('/\r\n|\r|\n/', (string) $this->benefits_text) ?: []
            )));

            $this->merge(['benefits' => $lines ?: null]);
        }
    }


    public function rules(): array
    {
        $tierId = $this->route('tier')?->id ?? $this->route('id');

        return [
            'slug' => 'required|string|max:64|unique:loyalty_tiers,slug,' . $tierId,
            'name' => 'required|string|max:255',
            'min_lifetime_spend' => 'required|numeric|min:0',
            'earn_multiplier' => 'required|numeric|min:0.01',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
            'benefits' => 'nullable|array',
            'benefits.*' => 'nullable|string|max:500',
        ];
    }
}
