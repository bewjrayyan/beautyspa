<?php

namespace Modules\TreatmentReservation\Support;

use Illuminate\Support\Arr;

class TreatmentReservationLang
{
    /**
     * Resolve a treatmentreservation translation with file fallback when cache/DB is stale.
     *
     * @param  array<string, string|int|float>  $replace
     */
    public static function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        $line = static::get($key, $locale);

        if ($replace === []) {
            return $line;
        }

        foreach ($replace as $search => $value) {
            $line = str_replace(':' . $search, (string) $value, $line);
        }

        return $line;
    }

    public static function get(string $key, ?string $locale = null): string
    {
        $fullKey = str_starts_with($key, 'treatmentreservation::')
            ? $key
            : 'treatmentreservation::' . $key;

        $locale = $locale ?? app()->getLocale();
        $value = trans($fullKey, [], $locale);

        if ($value !== $fullKey) {
            return $value;
        }

        if (! preg_match('/^treatmentreservation::([^.]+)\.(.+)$/', $fullKey, $matches)) {
            return $fullKey;
        }

        [, $group, $path] = $matches;

        $fromFile = static::fromFile($group, $path, $locale);

        return $fromFile ?? $fullKey;
    }

    public static function fromFile(string $group, string $path, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        foreach ([$locale, 'en'] as $tryLocale) {
            $file = base_path("modules/TreatmentReservation/Resources/lang/{$tryLocale}/{$group}.php");

            if (! is_file($file)) {
                continue;
            }

            $value = Arr::get(require $file, $path);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
