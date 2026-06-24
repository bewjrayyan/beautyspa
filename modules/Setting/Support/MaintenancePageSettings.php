<?php

namespace Modules\Setting\Support;

class MaintenancePageSettings
{
    public const PRESET_AESTHETIC = 'aesthetic';

    public const PRESET_MINIMAL = 'minimal';

    public const PRESET_CLASSIC = 'classic';

    public const PRESET_CUSTOM = 'custom';

    public const COLOR_STORE_THEME = 'store_theme';

    public const COLOR_CUSTOM = 'custom';


    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'maintenance_page_effect_preset' => self::PRESET_AESTHETIC,
            'maintenance_page_color_source' => self::COLOR_STORE_THEME,
            'maintenance_page_accent_color' => '#ff749f',
            'maintenance_page_gradient_enabled' => true,
            'maintenance_page_bokeh_enabled' => true,
            'maintenance_page_bokeh_count' => 12,
            'maintenance_page_shimmer_enabled' => true,
            'maintenance_page_grain_drift_enabled' => true,
            'maintenance_page_frosted_card_enabled' => true,
        ];
    }


    /**
     * @return array<string, string>
     */
    public static function presetOptions(): array
    {
        return [
            self::PRESET_AESTHETIC => trans('setting::settings.form.maintenance_page_preset_aesthetic'),
            self::PRESET_MINIMAL => trans('setting::settings.form.maintenance_page_preset_minimal'),
            self::PRESET_CLASSIC => trans('setting::settings.form.maintenance_page_preset_classic'),
            self::PRESET_CUSTOM => trans('setting::settings.form.maintenance_page_preset_custom'),
        ];
    }


    /**
     * @return array<string, string>
     */
    public static function colorSourceOptions(): array
    {
        return [
            self::COLOR_STORE_THEME => trans('setting::settings.form.maintenance_page_color_store_theme'),
            self::COLOR_CUSTOM => trans('setting::settings.form.maintenance_page_color_custom'),
        ];
    }


    /**
     * @return array<string, mixed>
     */
    public static function presetValues(string $preset): array
    {
        return match ($preset) {
            self::PRESET_MINIMAL => [
                'maintenance_page_gradient_enabled' => true,
                'maintenance_page_bokeh_enabled' => false,
                'maintenance_page_bokeh_count' => 6,
                'maintenance_page_shimmer_enabled' => false,
                'maintenance_page_grain_drift_enabled' => false,
                'maintenance_page_frosted_card_enabled' => true,
            ],
            self::PRESET_CLASSIC => [
                'maintenance_page_gradient_enabled' => true,
                'maintenance_page_bokeh_enabled' => true,
                'maintenance_page_bokeh_count' => 8,
                'maintenance_page_shimmer_enabled' => false,
                'maintenance_page_grain_drift_enabled' => true,
                'maintenance_page_frosted_card_enabled' => true,
            ],
            self::PRESET_AESTHETIC => [
                'maintenance_page_gradient_enabled' => true,
                'maintenance_page_bokeh_enabled' => true,
                'maintenance_page_bokeh_count' => 12,
                'maintenance_page_shimmer_enabled' => true,
                'maintenance_page_grain_drift_enabled' => true,
                'maintenance_page_frosted_card_enabled' => true,
            ],
            default => [],
        };
    }


    /**
     * @return array{
     *   gradient: bool,
     *   bokeh: bool,
     *   bokeh_count: int,
     *   shimmer: bool,
     *   grain_drift: bool,
     *   frosted_card: bool,
     *   theme_color: string
     * }
     */
    public static function resolved(): array
    {
        $defaults = self::defaults();

        return [
            'gradient' => self::toBool(setting('maintenance_page_gradient_enabled', $defaults['maintenance_page_gradient_enabled'])),
            'bokeh' => self::toBool(setting('maintenance_page_bokeh_enabled', $defaults['maintenance_page_bokeh_enabled'])),
            'bokeh_count' => self::clampBokehCount(setting('maintenance_page_bokeh_count', $defaults['maintenance_page_bokeh_count'])),
            'shimmer' => self::toBool(setting('maintenance_page_shimmer_enabled', $defaults['maintenance_page_shimmer_enabled'])),
            'grain_drift' => self::toBool(setting('maintenance_page_grain_drift_enabled', $defaults['maintenance_page_grain_drift_enabled'])),
            'frosted_card' => self::toBool(setting('maintenance_page_frosted_card_enabled', $defaults['maintenance_page_frosted_card_enabled'])),
            'theme_color' => self::accentColor(),
        ];
    }


    public static function accentColor(): string
    {
        $source = (string) setting('maintenance_page_color_source', self::COLOR_STORE_THEME);

        if ($source === self::COLOR_CUSTOM) {
            return self::normalizeHex((string) setting('maintenance_page_accent_color', '#ff749f'));
        }

        if (function_exists('storefront_theme_color')) {
            return self::normalizeHex((string) storefront_theme_color());
        }

        return '#ff749f';
    }


    public static function fingerprint(): string
    {
        return substr(hash('sha256', json_encode(self::resolved())), 0, 12);
    }


    /**
     * @return array<int, array{width:int,height:int,left:string,top:string,duration:int,delay:float}>
     */
    public static function bokehOrbs(int $count): array
    {
        $presets = [
            ['width' => 120, 'height' => 120, 'left' => '8%', 'top' => '18%', 'duration' => 11, 'delay' => -1],
            ['width' => 180, 'height' => 180, 'left' => '72%', 'top' => '10%', 'duration' => 13, 'delay' => -4],
            ['width' => 90, 'height' => 90, 'left' => '58%', 'top' => '62%', 'duration' => 9, 'delay' => -2],
            ['width' => 220, 'height' => 220, 'left' => '18%', 'top' => '68%', 'duration' => 15, 'delay' => -6],
            ['width' => 70, 'height' => 70, 'left' => '84%', 'top' => '72%', 'duration' => 8, 'delay' => -3],
            ['width' => 140, 'height' => 140, 'left' => '42%', 'top' => '28%', 'duration' => 12, 'delay' => -5],
            ['width' => 55, 'height' => 55, 'left' => '28%', 'top' => '42%', 'duration' => 7, 'delay' => -1.5],
            ['width' => 160, 'height' => 160, 'left' => '64%', 'top' => '38%', 'duration' => 10, 'delay' => -7],
            ['width' => 48, 'height' => 48, 'left' => '12%', 'top' => '78%', 'duration' => 6, 'delay' => -2.5],
            ['width' => 100, 'height' => 100, 'left' => '88%', 'top' => '34%', 'duration' => 11, 'delay' => -8],
            ['width' => 76, 'height' => 76, 'left' => '50%', 'top' => '8%', 'duration' => 9, 'delay' => -4.5],
            ['width' => 130, 'height' => 130, 'left' => '4%', 'top' => '52%', 'duration' => 14, 'delay' => -9],
        ];

        return array_slice($presets, 0, self::clampBokehCount($count));
    }


    /**
     * @return array<int, string>
     */
    public static function applyMissingOnly(): array
    {
        $applied = [];

        try {
            $existing = setting()->all();
        } catch (\Throwable) {
            $existing = [];
        }

        foreach (self::defaults() as $key => $value) {
            if (array_key_exists($key, $existing)) {
                continue;
            }

            setting([$key => $value]);
            $applied[] = $key;
        }

        return $applied;
    }


    private static function normalizeHex(string $color): string
    {
        $color = trim($color);

        if ($color === '') {
            return '#ff749f';
        }

        if ($color[0] !== '#') {
            $color = '#'.$color;
        }

        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color) === 1) {
            return $color;
        }

        return '#ff749f';
    }


    private static function toBool(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }


    private static function clampBokehCount(mixed $value): int
    {
        $count = (int) $value;

        return max(1, min(12, $count));
    }
}
