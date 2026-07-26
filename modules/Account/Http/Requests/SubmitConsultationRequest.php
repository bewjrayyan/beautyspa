<?php

namespace Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Services\ConsultationCustomerAccess;
use Modules\Account\Services\ConsultationRuleFactory;

class SubmitConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');

        return $submission instanceof ConsultationSubmission
            && $this->user() !== null
            && app(ConsultationCustomerAccess::class)->canAccess($submission, $this->user());
    }

    public function rules(): array
    {
        $submission = $this->route('submission');

        if (! $submission instanceof ConsultationSubmission) {
            return [];
        }

        return app(ConsultationRuleFactory::class)->forQuestions(
            $submission->questions_snapshot ?: []
        );
    }

    public function attributes(): array
    {
        $submission = $this->route('submission');
        $attributes = [
            'consent_accepted' => trans('account::consultation.fields.consent'),
            'legal_consent_accepted' => trans('account::consultation.fields.legal_consent'),
            'signature_data' => trans('account::consultation.fields.signature'),
        ];

        foreach (($submission?->questions_snapshot ?: []) as $question) {
            if (($question['key'] ?? '') !== '' && ($question['type'] ?? null) !== 'section') {
                $attributes['answers.' . $question['key']] = (string) $question['label'];
            }
        }

        return $attributes;
    }

    public function answers(): array
    {
        return $this->validated('answers', []);
    }
}
