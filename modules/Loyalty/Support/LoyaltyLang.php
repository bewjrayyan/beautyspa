<?php

namespace Modules\Loyalty\Support;

use Illuminate\Support\Arr;

class LoyaltyLang
{
    /**
     * Resolve a loyalty translation with file fallback when cache/DB is stale.
     */
    public static function get(string $key, ?string $locale = null): string
    {
        $fullKey = str_starts_with($key, 'loyalty::') ? $key : 'loyalty::' . $key;
        $locale = $locale ?? app()->getLocale();
        $value = trans($fullKey, [], $locale);

        if ($value !== $fullKey) {
            return $value;
        }

        if (! preg_match('/^loyalty::([^.]+)\.(.+)$/', $fullKey, $matches)) {
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
            $file = base_path("modules/Loyalty/Resources/lang/{$tryLocale}/{$group}.php");

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
