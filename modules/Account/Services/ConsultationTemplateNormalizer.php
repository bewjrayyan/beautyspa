<?php

namespace Modules\Account\Services;

use Illuminate\Support\Str;

class ConsultationTemplateNormalizer
{
    public function questions(array $questions): array
    {
        $normalized = collect($questions)
            ->filter(fn (mixed $question): bool => (
                is_array($question) && filled($question['label'] ?? null)
            ))
            ->map(fn (array $question): array => $this->question($question))
            ->values()
            ->all();

        return $this->normalizeConditions($normalized);
    }

    private function question(array $question): array
    {
        $type = (string) ($question['type'] ?? 'text');
        $options = preg_split('/[\r\n]+/', (string) ($question['options'] ?? '')) ?: [];

        return [
            'key' => $this->key((string) ($question['key'] ?? '')),
            'label' => trim((string) $question['label']),
            'type' => $type,
            'placeholder' => trim((string) ($question['placeholder'] ?? '')),
            'required' => $type !== 'section'
                && filter_var($question['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'options' => collect($options)
                ->map(fn (string $option): string => trim($option))
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'condition' => [
                'enabled' => $type !== 'section'
                    && filter_var(data_get($question, 'condition.enabled', false), FILTER_VALIDATE_BOOLEAN),
                'source_key' => $this->cleanKey((string) data_get($question, 'condition.source_key', '')),
                'operator' => (string) data_get($question, 'condition.operator', 'equals'),
                'value' => trim((string) data_get($question, 'condition.value', '')),
            ],
        ];
    }

    private function normalizeConditions(array $questions): array
    {
        $availableKeys = [];

        foreach ($questions as &$question) {
            $condition = $question['condition'];
            $valid = $condition['enabled']
                && in_array($condition['source_key'], $availableKeys, true)
                && in_array($condition['operator'], ConsultationConditionEvaluator::OPERATORS, true)
                && $condition['value'] !== '';

            $question['condition'] = $valid
                ? $condition
                : ['enabled' => false, 'source_key' => '', 'operator' => 'equals', 'value' => ''];

            if ($question['type'] !== 'section') {
                $availableKeys[] = $question['key'];
            }
        }
        unset($question);

        return $questions;
    }

    private function key(string $key): string
    {
        return $this->cleanKey($key) ?: 'q_' . Str::lower(Str::random(12));
    }

    private function cleanKey(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $key) ?: '';
    }
}
