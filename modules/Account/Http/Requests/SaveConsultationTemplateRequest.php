<?php

namespace Modules\Account\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;

class SaveConsultationTemplateRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'is_active' => ['required', 'boolean'],
            'title' => ['required', 'string', 'max:190'],
            'intro' => ['nullable', 'string', 'max:5000'],
            'consent' => ['required', 'string', 'max:5000'],
            'questions' => ['required', 'array', 'min:1', 'max:50'],
            'questions.*.key' => ['required', 'string', 'max:80', 'distinct'],
            'questions.*.label' => ['required', 'string', 'max:500'],
            'questions.*.type' => ['required', Rule::in(['section', 'text', 'textarea', 'yes_no', 'select', 'checkbox', 'date', 'body_map'])],
            'questions.*.required' => ['required', 'boolean'],
            'questions.*.options' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
