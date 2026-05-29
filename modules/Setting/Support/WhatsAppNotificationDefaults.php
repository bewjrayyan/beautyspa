<?php

namespace Modules\Setting\Support;

use Modules\Setting\Entities\Setting;

class WhatsAppNotificationDefaults
{
    public static function all(): array
    {
        return config('setting.whatsapp_notifications', []);
    }

    public static function applyMissingOnly(): array
    {
        $applied = [];

        foreach (self::all() as $key => $value) {
            if (! self::shouldApply($key, $value)) {
                continue;
            }

            Setting::set($key, $value);
            $applied[] = $key;
        }

        return array_merge($applied, self::applyRecommendedToggles());
    }

    /**
     * Enable notification toggles that are still off (0/false), using config defaults.
     */
    public static function applyRecommendedToggles(): array
    {
        $defaults = self::all();
        $applied = [];

        $toggleKeys = [
            'welcome_sms',
            'whatsapp_customer_reminder_enabled',
            'whatsapp_customer_followup_enabled',
            'new_order_admin_sms',
            'new_order_sms',
            'whatsapp_completed_group_enabled',
            'whatsapp_completed_beautician_enabled',
            'whatsapp_beautician_new_booking_enabled',
            'whatsapp_beautician_reminder_enabled',
        ];

        foreach ($toggleKeys as $key) {
            if (! array_key_exists($key, $defaults)) {
                continue;
            }

            if (! self::isFalsy(Setting::has($key) ? Setting::get($key) : null)) {
                continue;
            }

            Setting::set($key, (bool) $defaults[$key]);
            $applied[] = $key;
        }

        $statuses = Setting::has('sms_order_statuses') ? Setting::get('sms_order_statuses') : null;

        if (! is_array($statuses) || count($statuses) < 2) {
            Setting::set('sms_order_statuses', $defaults['sms_order_statuses'] ?? []);
            $applied[] = 'sms_order_statuses';
        }

        return $applied;
    }

    protected static function isFalsy(mixed $value): bool
    {
        return $value === null
            || $value === ''
            || $value === false
            || $value === 0
            || $value === '0';
    }

    protected static function shouldApply(string $key, mixed $default): bool
    {
        if ($key === 'onesender_api_key' && $default === '') {
            return false;
        }

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

        if (is_array($current) && $current === []) {
            return true;
        }

        return false;
    }
}
