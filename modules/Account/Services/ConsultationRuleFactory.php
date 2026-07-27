<?php

namespace Modules\Account\Services;

use Illuminate\Validation\Rule;
use Modules\Account\Rules\ValidPngSignature;

class ConsultationRuleFactory
{
    public function __construct(private readonly ConsultationConditionEvaluator $conditions)
    {
    }

    public function forQuestions(array $questions, array $answers = []): array
    {
        $rules = [
            'answers' => ['nullable', 'array'],
            'consent_accepted' => ['accepted'],
            'legal_consent_accepted' => ['accepted'],
            'signature_data' => ['required', 'string', 'max:1500000', new ValidPngSignature()],
        ];

        $visibility = $this->conditions->visibilityMap($questions, $answers);

        foreach ($questions as $question) {
            $this->appendQuestionRules($rules, $question, $visibility);
        }

        return $rules;
    }

    private function appendQuestionRules(array &$rules, array $question, array $visibility): void
    {
        $key = (string) ($question['key'] ?? '');
        $type = (string) ($question['type'] ?? 'text');

        if ($key === '' || $type === 'section') {
            return;
        }

        $attribute = "answers.{$key}";
        $visible = $visibility[$key] ?? true;

        if (! $visible) {
            $rules[$attribute] = ['prohibited'];

            return;
        }

        $presence = ! empty($question['required']) ? 'required' : 'nullable';
        $options = array_values(array_filter(
            $question['options'] ?? [],
            fn (mixed $option): bool => is_string($option) && $option !== ''
        ));

        $rules[$attribute] = match ($type) {
            'yes_no' => [$presence, Rule::in(['yes', 'no'])],
            'select' => [$presence, Rule::in($options)],
            'date' => [$presence, 'date_format:Y-m-d'],
            'checkbox', 'body_map' => [$presence, 'array', 'max:' . max(1, count($options))],
            'textarea' => [$presence, 'string', 'max:5000'],
            default => [$presence, 'string', 'max:1000'],
        };

        if (in_array($type, ['checkbox', 'body_map'], true)) {
            $rules["{$attribute}.*"] = ['string', 'distinct', Rule::in($options)];
        }
    }
}
