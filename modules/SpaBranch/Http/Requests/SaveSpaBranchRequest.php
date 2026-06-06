<?php

namespace Modules\SpaBranch\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;

class SaveSpaBranchRequest extends Request
{
    protected $availableAttributes = 'spabranch::attributes';

    protected function prepareForValidation(): void
    {
        if ($this->input('position') === '' || $this->input('position') === null) {
            $this->merge(['position' => 0]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('spa_branches', 'code')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'beauticians' => ['nullable', 'array'],
            'beauticians.*' => [
                'integer',
                Rule::exists('beauticians', 'id')->where('is_active', true),
            ],
        ];
    }
}
