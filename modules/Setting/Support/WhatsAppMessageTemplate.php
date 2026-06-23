<?php

namespace Modules\Setting\Support;

class WhatsAppMessageTemplate
{
    /**
     * @param  array<string, string>  $replacements
     */
    public static function render(string $settingKey, array $replacements, ?string $fallback = null): string
    {
        $template = trim((string) setting($settingKey, ''));

        if ($template === '') {
            $template = trim((string) (config('setting.whatsapp_notifications.' . $settingKey) ?? ''));
        }

        if ($template === '') {
            return $fallback !== null
                ? self::normalize(self::applyReplacements($fallback, $replacements))
                : '';
        }

        $message = self::normalize(self::applyReplacements($template, $replacements));

        if ($message === '' && $fallback !== null) {
            return self::normalize(self::applyReplacements($fallback, $replacements));
        }

        return $message;
    }


    /**
     * @param  array<string, string>  $replacements
     */
    public static function applyReplacements(string $template, array $replacements): string
    {
        $keys = array_keys($replacements);

        usort($keys, fn (string $a, string $b) => strlen($b) <=> strlen($a));

        $search = [];
        $replace = [];

        foreach ($keys as $key) {
            $search[] = ':' . ltrim((string) $key, ':');
            $replace[] = (string) $replacements[$key];
        }

        return str_replace($search, $replace, $template);
    }


    private static function normalize(string $message): string
    {
        $message = trim($message);

        if ($message === '') {
            return '';
        }

        return preg_replace("/\n{3,}/", "\n\n", $message) ?? $message;
    }
}
