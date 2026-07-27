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
            'questions.*.placeholder' => ['nullable', 'string', 'max:250'],
            'questions.*.required' => ['required', 'boolean'],
            'questions.*.options' => ['nullable', 'string', 'max:5000'],
            'questions.*.condition.enabled' => ['required', 'boolean'],
            'questions.*.condition.source_key' => [
                'required_if:questions.*.condition.enabled,1',
                'nullable',
                'string',
                'max:80',
            ],
            'questions.*.condition.operator' => [
                'required_if:questions.*.condition.enabled,1',
                'nullable',
                Rule::in(['equals', 'not_equals', 'contains', 'not_contains']),
            ],
            'questions.*.condition.value' => [
                'required_if:questions.*.condition.enabled,1',
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'questions.*.placeholder' => trans(
                'product::consultation_forms.validation_attributes.placeholder'
            ),
            'questions.*.condition.source_key' => trans(
                'product::consultation_forms.validation_attributes.condition_source'
            ),
            'questions.*.condition.operator' => trans(
                'product::consultation_forms.validation_attributes.condition_operator'
            ),
            'questions.*.condition.value' => trans(
                'product::consultation_forms.validation_attributes.condition_value'
            ),
        ];
    }

    public function messages(): array
    {
        return [
            'questions.*.condition.source_key.required_if' => trans(
                'product::consultation_forms.validation.condition_source_required'
            ),
            'questions.*.condition.operator.required_if' => trans(
                'product::consultation_forms.validation.condition_operator_required'
            ),
            'questions.*.condition.value.required_if' => trans(
                'product::consultation_forms.validation.condition_value_required'
            ),
        ];
    }
}
