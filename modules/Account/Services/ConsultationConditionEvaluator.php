<?php

namespace Modules\Account\Services;

class ConsultationConditionEvaluator
{
    public const OPERATORS = ['equals', 'not_equals', 'contains', 'not_contains'];

    public function visibilityMap(array $questions, array $answers): array
    {
        $visibility = [];

        foreach ($questions as $question) {
            $key = (string) ($question['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $visibility[$key] = $this->isVisible($question, $answers, $visibility);
        }

        return $visibility;
    }

    public function visibleAnswers(array $questions, array $answers): array
    {
        $visibility = $this->visibilityMap($questions, $answers);
        $allowedKeys = collect($questions)
            ->filter(fn (array $question): bool => ($question['type'] ?? null) !== 'section')
            ->pluck('key')
            ->filter(fn (mixed $key): bool => ($visibility[$key] ?? false) === true)
            ->all();

        return collect($answers)->only($allowedKeys)->all();
    }

    private function isVisible(array $question, array $answers, array $visibility): bool
    {
        $condition = $question['condition'] ?? [];

        if (! ($condition['enabled'] ?? false)) {
            return true;
        }

        $sourceKey = (string) ($condition['source_key'] ?? '');

        if ($sourceKey === '' || ($visibility[$sourceKey] ?? false) !== true) {
            return false;
        }

        $answer = $answers[$sourceKey] ?? null;

        if (! $this->hasAnswer($answer)) {
            return false;
        }

        return $this->matches(
            $answer,
            (string) ($condition['operator'] ?? 'equals'),
            (string) ($condition['value'] ?? '')
        );
    }

    private function matches(mixed $answer, string $operator, string $expected): bool
    {
        $matches = match ($operator) {
            'contains', 'not_contains' => $this->contains($answer, $expected),
            default => $this->equals($answer, $expected),
        };

        return in_array($operator, ['not_equals', 'not_contains'], true) ? ! $matches : $matches;
    }

    private function equals(mixed $answer, string $expected): bool
    {
        if (is_array($answer)) {
            return count($answer) === 1 && $this->normalize((string) reset($answer)) === $this->normalize($expected);
        }

        return $this->normalize((string) $answer) === $this->normalize($expected);
    }

    private function contains(mixed $answer, string $expected): bool
    {
        if (is_array($answer)) {
            return collect($answer)->contains(
                fn (mixed $value): bool => $this->normalize((string) $value) === $this->normalize($expected)
            );
        }

        return str_contains($this->normalize((string) $answer), $this->normalize($expected));
    }

    private function normalize(string $value): string
    {
        $normalized = mb_strtolower(trim($value));

        return match ($normalized) {
            'ya' => 'yes',
            'tidak' => 'no',
            default => $normalized,
        };
    }

    private function hasAnswer(mixed $answer): bool
    {
        if (is_array($answer)) {
            return collect($answer)->contains(fn (mixed $value): bool => $this->normalize((string) $value) !== '');
        }

        return $this->normalize((string) $answer) !== '';
    }
}
