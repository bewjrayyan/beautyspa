<?php

namespace Modules\SpecialGift\Support;

class SpecialGiftPageSettings
{
    public const PRESET_AESTHETIC = 'aesthetic';

    public const PRESET_MINIMAL = 'minimal';

    public const PRESET_CLASSIC = 'classic';

    public const PRESET_CUSTOM = 'custom';

    public const COLOR_STORE_THEME = 'store_theme';

    public const COLOR_CUSTOM = 'custom';

    /**
     * @return list<string>
     */
    public static function contentKeys(): array
    {
        return [
            'specialgift_page_title',
            'specialgift_page_tagline',
            'specialgift_page_lead',
            'specialgift_step_order',
            'specialgift_step_details',
            'specialgift_step_send',
            'specialgift_form_title',
            'specialgift_submit_label',
            'specialgift_trust_note',
            'specialgift_preview_label',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'specialgift_page_preset' => self::PRESET_AESTHETIC,
            'specialgift_page_color_source' => self::COLOR_STORE_THEME,
            'specialgift_page_accent_color' => '#f274ac',
            'specialgift_page_gradient_enabled' => true,
            'specialgift_page_bokeh_enabled' => true,
            'specialgift_page_sparkles_enabled' => true,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function presetOptions(): array
    {
        return [
            self::PRESET_AESTHETIC => trans('specialgift::admin.design_preset_aesthetic'),
            self::PRESET_MINIMAL => trans('specialgift::admin.design_preset_minimal'),
            self::PRESET_CLASSIC => trans('specialgift::admin.design_preset_classic'),
            self::PRESET_CUSTOM => trans('specialgift::admin.design_preset_custom'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function colorSourceOptions(): array
    {
        return [
            self::COLOR_STORE_THEME => trans('specialgift::admin.design_color_store_theme'),
            self::COLOR_CUSTOM => trans('specialgift::admin.design_color_custom'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function presetValues(string $preset): array
    {
        return match ($preset) {
            self::PRESET_MINIMAL => [
                'specialgift_page_gradient_enabled' => true,
                'specialgift_page_bokeh_enabled' => false,
                'specialgift_page_sparkles_enabled' => false,
            ],
            self::PRESET_CLASSIC => [
                'specialgift_page_gradient_enabled' => true,
                'specialgift_page_bokeh_enabled' => true,
                'specialgift_page_sparkles_enabled' => false,
            ],
            self::PRESET_AESTHETIC => [
                'specialgift_page_gradient_enabled' => true,
                'specialgift_page_bokeh_enabled' => true,
                'specialgift_page_sparkles_enabled' => true,
            ],
            default => [],
        };
    }

    /**
     * @return array{
     *   preset: string,
     *   gradient: bool,
     *   bokeh: bool,
     *   sparkles: bool,
     *   accent_color: string
     * }
     */
    public static function resolved(): array
    {
        $defaults = self::defaults();

        return [
            'preset' => (string) setting('specialgift_page_preset', $defaults['specialgift_page_preset']),
            'gradient' => self::toBool(setting('specialgift_page_gradient_enabled', $defaults['specialgift_page_gradient_enabled'])),
            'bokeh' => self::toBool(setting('specialgift_page_bokeh_enabled', $defaults['specialgift_page_bokeh_enabled'])),
            'sparkles' => self::toBool(setting('specialgift_page_sparkles_enabled', $defaults['specialgift_page_sparkles_enabled'])),
            'accent_color' => self::accentColor(),
        ];
    }

    public static function accentColor(): string
    {
        $source = (string) setting('specialgift_page_color_source', self::COLOR_STORE_THEME);

        if ($source === self::COLOR_CUSTOM) {
            return self::normalizeHex((string) setting('specialgift_page_accent_color', '#f274ac'));
        }

        if (function_exists('storefront_theme_color')) {
            return self::normalizeHex((string) storefront_theme_color());
        }

        return '#f274ac';
    }

    /**
     * @return array<string, mixed>
     */
    public static function formSettings(): array
    {
        try {
            $existing = setting()->all();
        } catch (\Throwable) {
            $existing = [];
        }

        return array_merge(self::defaults(), $existing);
    }

    /**
     * @return list<string>
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
            return '#f274ac';
        }

        if ($color[0] !== '#') {
            $color = '#'.$color;
        }

        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color) === 1) {
            return $color;
        }

        return '#f274ac';
    }

    private static function toBool(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
