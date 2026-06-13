<?php

namespace Modules\Beautician\Support;

class TitleCase
{
    public static function format(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/u', ' ', trim($value));

        if ($normalized === '') {
            return '';
        }

        return preg_replace_callback(
            '/[^\s\/]+/u',
            static fn (array $match): string => mb_strtoupper(mb_substr($match[0], 0, 1, 'UTF-8'), 'UTF-8')
                .mb_strtolower(mb_substr($match[0], 1, null, 'UTF-8'), 'UTF-8'),
            mb_strtolower($normalized, 'UTF-8')
        );
    }
}
