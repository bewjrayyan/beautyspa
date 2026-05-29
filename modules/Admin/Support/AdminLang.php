<?php

namespace Modules\Admin\Support;

use Illuminate\Support\Arr;

class AdminLang
{
    public static function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $fullKey = str_starts_with($key, 'admin::') ? $key : 'admin::' . $key;
        $locale = $locale ?? app()->getLocale();
        $value = trans($fullKey, $replace, $locale);

        if ($value !== $fullKey) {
            return $value;
        }

        if (! preg_match('/^admin::([^.]+)\.(.+)$/', $fullKey, $matches)) {
            return $fullKey;
        }

        $fromFile = static::fromFile($matches[1], $matches[2], $locale);

        if ($fromFile === null) {
            return $fullKey;
        }

        foreach ($replace as $placeholder => $replacement) {
            $fromFile = str_replace(':' . $placeholder, (string) $replacement, $fromFile);
        }

        return $fromFile;
    }


    public static function fromFile(string $group, string $path, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        foreach ([$locale, 'en'] as $tryLocale) {
            $file = base_path("modules/Admin/Resources/lang/{$tryLocale}/{$group}.php");

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
