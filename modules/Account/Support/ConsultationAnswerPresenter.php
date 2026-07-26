<?php

namespace Modules\Account\Support;

class ConsultationAnswerPresenter
{
    public static function display(mixed $answer): string
    {
        if (is_array($answer)) {
            return implode(', ', $answer);
        }

        return match ($answer) {
            'yes' => trans('account::consultation.yes'),
            'no' => trans('account::consultation.no'),
            null, '' => '—',
            default => (string) $answer,
        };
    }
}
