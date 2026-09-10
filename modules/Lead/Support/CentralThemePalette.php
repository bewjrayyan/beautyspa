<?php

declare(strict_types=1);

namespace Modules\Lead\Support;

/**
 * Sidebar colors from Admin settings; main UI uses fixed CSS palette.
 */
final class CentralThemePalette
{
    /**
     * @return array{sidebar_bg: string, sidebar_bg_2: string, sidebar_accent: string, sidebar_accent_2: string}
     */
    public static function sidebarFromSettings(): array
    {
        $bg = self::hex(setting('admin_sidebar_color'), '#222530');
        $accent = self::hex(setting('admin_sidebar_accent_color'), '#475aff');

        return [
            'sidebar_bg' => $bg,
            'sidebar_bg_2' => self::mix($bg, '#000000', 0.28),
            'sidebar_accent' => $accent,
            'sidebar_accent_2' => self::mix($accent, '#ffffff', 0.22),
        ];
    }

    private static function hex(?string $value, string $fallback): string
    {
        $value = trim((string) $value);

        if ($value !== '' && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
            if (strlen($value) === 4) {
                return sprintf('#%s%s%s%s%s%s', $value[1], $value[1], $value[2], $value[2], $value[3], $value[3]);
            }

            return strtolower($value);
        }

        return strtolower($fallback);
    }

    /** @return array{r:int,g:int,b:int} */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function mix(string $hex, string $with, float $ratio): string
    {
        $a = self::rgb($hex);
        $b = self::rgb(self::hex($with, '#ffffff'));
        $ratio = max(0.0, min(1.0, $ratio));

        return sprintf(
            '#%02x%02x%02x',
            (int) round($a['r'] * (1 - $ratio) + $b['r'] * $ratio),
            (int) round($a['g'] * (1 - $ratio) + $b['g'] * $ratio),
            (int) round($a['b'] * (1 - $ratio) + $b['b'] * $ratio)
        );
    }
}
