<?php

namespace Modules\Account\Services;

use Illuminate\Support\Str;

class ConsultationTemplateNormalizer
{
    public function questions(array $questions): array
    {
        return collect($questions)
            ->filter(fn (mixed $question): bool => (
                is_array($question) && filled($question['label'] ?? null)
            ))
            ->map(fn (array $question): array => $this->question($question))
            ->values()
            ->all();
    }

    private function question(array $question): array
    {
        $type = (string) ($question['type'] ?? 'text');
        $options = preg_split('/[\r\n]+/', (string) ($question['options'] ?? '')) ?: [];

        return [
            'key' => $this->key((string) ($question['key'] ?? '')),
            'label' => trim((string) $question['label']),
            'type' => $type,
            'required' => $type !== 'section'
                && filter_var($question['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'options' => collect($options)
                ->map(fn (string $option): string => trim($option))
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ];
    }

    private function key(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $key)
            ?: 'q_' . Str::lower(Str::random(12));
    }
}
