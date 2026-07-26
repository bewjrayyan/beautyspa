<?php

namespace Modules\Account\Support;

use Illuminate\Support\Facades\Lang;

class ConsultationQuestionLabel
{
    public static function parts(array $question): array
    {
        $label = trim((string) ($question['label'] ?? ''));
        $parts = preg_split('/\s+\/\s+/', $label, 2);

        if (count($parts) === 2) {
            return ['primary' => trim($parts[0]), 'english' => trim($parts[1])];
        }

        $key = (string) ($question['key'] ?? '');
        $translationKey = "account::consultation.question_labels.{$key}";
        $english = $key !== '' ? Lang::get($translationKey, [], 'en') : null;

        return [
            'primary' => $label,
            'english' => $english !== $translationKey ? $english : null,
        ];
    }
}
