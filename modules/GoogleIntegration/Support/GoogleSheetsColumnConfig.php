<?php

namespace Modules\GoogleIntegration\Support;

use Modules\Order\Entities\Order;

class GoogleSheetsColumnConfig
{
    /**
     * @return array<string, bool>
     */
    public static function definitions(): array
    {
        $columns = [
            'order_id' => true,
            'order_date' => true,
            'status' => true,
            'customer_name' => true,
            'customer_email' => true,
            'customer_phone' => true,
            'beautician' => true,
            'beautician_phone' => true,
            'appointment_date' => true,
            'appointment_time' => true,
            'treatments' => true,
            'subtotal' => true,
            'discount' => true,
            'shipping' => true,
            'tax' => true,
            'total' => true,
            'payment_method' => true,
            'coupon' => true,
            'order_note' => true,
            'synced_at' => true,
        ];

        if (is_module_enabled('SpaBranch')) {
            $columns['spa_branch'] = true;
        }

        return $columns;
    }


    /**
     * @return array<int, string>
     */
    public static function defaultEnabledKeys(): array
    {
        return array_keys(self::definitions());
    }


    /**
     * @return array<int, string>
     */
    public static function enabledKeys(): array
    {
        return self::parseStoredKeys(setting('google_sheets_columns'));
    }


    /**
     * @return array<int, string>
     */
    public static function enabledKeysForStatus(string $status): array
    {
        if ((bool) setting('google_sheets_per_status_columns_enabled', false)) {
            $override = self::parseStatusOverride(setting(self::statusColumnsKey($status)));

            if ($override !== null && $override !== []) {
                return $override;
            }
        }

        return self::enabledKeys();
    }


    public static function statusColumnsKey(string $status): string
    {
        return 'google_sheets_columns_' . $status;
    }


    /**
     * @return array<int, string>
     */
    public static function statusSettingKeys(): array
    {
        $keys = [];

        foreach (array_keys(GoogleSheetsStatusConfig::defaults()) as $status) {
            $keys[] = self::statusColumnsKey($status);
        }

        return $keys;
    }


    public static function label(string $key): string
    {
        $translationKey = "setting::settings.form.google_sheets_columns.{$key}";

        return trans($translationKey) === $translationKey
            ? $key
            : trans($translationKey);
    }


    /**
     * @return array<int, string>
     */
    public static function headerLabelsForStatus(string $status): array
    {
        return array_map(
            fn (string $key) => self::label($key),
            self::enabledKeysForStatus($status),
        );
    }


    public static function applyMissingOnly(): void
    {
        if (setting('google_sheets_columns') === null || setting('google_sheets_columns') === '') {
            setting([
                'google_sheets_columns' => json_encode(self::defaultEnabledKeys()),
            ]);
        }

        if (setting('google_sheets_per_status_columns_enabled') === null) {
            setting(['google_sheets_per_status_columns_enabled' => false]);
        }

        if (setting('google_sheets_sync_alert_enabled') === null) {
            setting(['google_sheets_sync_alert_enabled' => false]);
        }

        if (setting('google_sheets_sync_alert_whatsapp_enabled') === null) {
            setting(['google_sheets_sync_alert_whatsapp_enabled' => false]);
        }
    }


    /**
     * @return array<int, string>|null Null when unset; empty array when explicitly using global columns.
     */
    private static function parseStatusOverride(mixed $stored): ?array
    {
        if ($stored === null || $stored === '') {
            return null;
        }

        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        if (! is_array($decoded)) {
            return null;
        }

        $valid = array_keys(self::definitions());

        return array_values(array_unique(array_filter(
            $decoded,
            fn ($key) => is_string($key) && in_array($key, $valid, true),
        )));
    }


    /**
     * @return array<int, string>
     */
    private static function parseStoredKeys(mixed $stored): array
    {
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        if (! is_array($decoded)) {
            return self::defaultEnabledKeys();
        }

        $valid = array_keys(self::definitions());

        $keys = array_values(array_unique(array_filter(
            $decoded,
            fn ($key) => is_string($key) && in_array($key, $valid, true),
        )));

        return $keys === [] ? self::defaultEnabledKeys() : $keys;
    }
}
