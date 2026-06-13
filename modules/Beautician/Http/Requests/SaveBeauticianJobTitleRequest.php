<?php

namespace Modules\Beautician\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Beautician\Support\TitleCase;
use Modules\Core\Http\Requests\Request;

class SaveBeauticianJobTitleRequest extends Request
{
    protected $availableAttributes = 'beautician::attributes';


    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => TitleCase::format($this->input('name')),
            ]);
        }
    }


    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('beautician_job_titles', 'name')->ignore($id),
            ],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
