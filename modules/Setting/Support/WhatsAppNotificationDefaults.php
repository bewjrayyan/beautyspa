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

        return $applied;
    }


    /**
     * @return list<string>
     */
    public static function messageTemplateKeys(): array
    {
        return [
            'whatsapp_welcome_message',
            'whatsapp_customer_reminder_message',
            'whatsapp_customer_completed_message',
            'whatsapp_customer_followup_message',
            'whatsapp_new_order_admin_message',
            'whatsapp_new_order_customer_message',
            'whatsapp_order_status_message',
            'whatsapp_status_beautician_message',
            'whatsapp_completed_group_message',
            'whatsapp_completed_beautician_message',
            'whatsapp_beautician_new_booking_message',
            'whatsapp_beautician_reminder_message',
        ];
    }


    /**
     * @return list<string>
     */
    public static function orderMessageTemplateKeys(): array
    {
        return [
            'whatsapp_new_order_admin_message',
            'whatsapp_new_order_customer_message',
            'whatsapp_order_status_message',
            'whatsapp_status_beautician_message',
            'whatsapp_completed_group_message',
            'whatsapp_completed_beautician_message',
        ];
    }


    /**
     * @return list<string>
     */
    public static function refreshMessageTemplates(bool $orderOnly = false, bool $force = false): array
    {
        $defaults = self::all();
        $keys = $orderOnly ? self::orderMessageTemplateKeys() : self::messageTemplateKeys();
        $applied = [];

        foreach ($keys as $key) {
            if (! isset($defaults[$key]) || ! is_string($defaults[$key]) || trim($defaults[$key]) === '') {
                continue;
            }

            if (! $force && Setting::has($key)) {
                $current = Setting::get($key);

                if (is_string($current) && trim($current) !== '') {
                    continue;
                }
            }

            Setting::set($key, $defaults[$key]);
            $applied[] = $key;
        }

        return $applied;
    }

    /**
     * Apply recommended notification toggles only when they do not exist yet.
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
            'whatsapp_status_notify_customer_enabled',
            'whatsapp_status_notify_beautician_enabled',
            'whatsapp_beautician_new_booking_enabled',
            'whatsapp_beautician_reminder_enabled',
        ];

        foreach ($toggleKeys as $key) {
            if (! array_key_exists($key, $defaults)) {
                continue;
            }

            if (Setting::has($key)) {
                continue;
            }

            Setting::set($key, (bool) $defaults[$key]);
            $applied[] = $key;
        }

        if (! Setting::has('sms_order_statuses')) {
            Setting::set('sms_order_statuses', $defaults['sms_order_statuses'] ?? []);
            $applied[] = 'sms_order_statuses';
        }

        return $applied;
    }

    protected static function shouldApply(string $key, mixed $default): bool
    {
        if ($key === 'onesender_api_key' && $default === '') {
            return false;
        }

        return ! Setting::has($key);
    }
}
