<?php

namespace Modules\Loyalty\Support;

use Modules\Setting\Entities\Setting;

class LoyaltySettingsDefaults
{
    public static function all(): array
    {
        return config('loyalty.settings_defaults', []);
    }

    public static function value(string $loyaltyKey): mixed
    {
        $key = str_starts_with($loyaltyKey, 'loyalty_')
            ? $loyaltyKey
            : 'loyalty_' . $loyaltyKey;

        return static::all()[$key] ?? null;
    }

    public static function forForm(string $loyaltyKey, array $settings = []): mixed
    {
        $key = str_starts_with($loyaltyKey, 'loyalty_')
            ? $loyaltyKey
            : 'loyalty_' . $loyaltyKey;

        $current = array_get($settings, $key);

        if ($current !== null && $current !== '') {
            return $current;
        }

        return static::value($key);
    }

    public static function applyMissingOnly(): array
    {
        $applied = [];

        foreach (static::all() as $key => $value) {
            if (! self::shouldApply($key, $value)) {
                continue;
            }

            Setting::set($key, $value);
            $applied[] = $key;
        }

        return array_merge($applied, static::applyRecommendedToggles());
    }

    public static function applyRecommendedToggles(): array
    {
        $defaults = static::all();
        $applied = [];
        $toggleKeys = [
            'loyalty_allow_with_coupon',
            'loyalty_birthday_bonus_enabled',
            'loyalty_referral_enabled',
            'loyalty_whatsapp_tier_upgrade',
            'loyalty_whatsapp_points_earned',
            'loyalty_whatsapp_points_expiring',
            'loyalty_whatsapp_birthday_bonus',
            'loyalty_whatsapp_referral_bonus',
        ];

        foreach ($toggleKeys as $key) {
            if (! isset($defaults[$key])) {
                continue;
            }

            if (! self::isFalsy(Setting::has($key) ? Setting::get($key) : null)) {
                continue;
            }

            Setting::set($key, (bool) $defaults[$key]);
            $applied[] = $key;
        }

        return $applied;
    }

    protected static function shouldApply(string $key, mixed $default): bool
    {
        if (! Setting::has($key)) {
            return true;
        }

        $current = Setting::get($key);

        if ($current === null) {
            return true;
        }

        if (is_string($current) && trim($current) === '') {
            return true;
        }

        return false;
    }

    protected static function isFalsy(mixed $value): bool
    {
        return $value === null
            || $value === ''
            || $value === false
            || $value === 0
            || $value === '0';
    }
}
