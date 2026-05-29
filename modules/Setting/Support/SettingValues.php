<?php

namespace Modules\Setting\Support;

use Modules\Setting\Entities\Setting;

class SettingValues
{
    /**
     * Read a setting directly from the database (not the request singleton).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (! config('app.installed')) {
            return $default;
        }

        try {
            $setting = Setting::query()->where('key', $key)->first();

            return $setting?->value ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }


    public static function isTruthy(string $key, bool $default = false): bool
    {
        return self::toBoolean(self::get($key, $default), $default);
    }


    public static function toBoolean(mixed $value, bool $default = false): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        if (! is_string($value)) {
            return $default;
        }

        $normalized = strtolower(trim($value));

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off', ''], true)) {
            return false;
        }

        return $default;
    }
}
