<?php

namespace Modules\Report\Support;

final class ReportSpreadsheetSanitizer
{
    /**
     * @param  array<int, string|int|float|null>  $row
     * @return array<int, string|int|float|null>
     */
    public static function sanitizeRow(array $row): array
    {
        return array_map(static::sanitize(...), $row);
    }


    public static function sanitize(string|int|float|null $value): string|int|float|null
    {
        if (! is_string($value)) {
            return $value;
        }

        if (preg_match('/^[\s\x{0000}-\x{001F}]*[=+\-@]/u', $value) === 1) {
            return "'" . $value;
        }

        return $value;
    }
}
