<?php

namespace Modules\GoogleIntegration\Support;

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
        $stored = setting('google_sheets_columns');
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
    public static function headerLabels(): array
    {
        return array_map(
            fn (string $key) => self::label($key),
            self::enabledKeys(),
        );
    }


    public static function applyMissingOnly(): void
    {
        if (setting('google_sheets_columns') !== null && setting('google_sheets_columns') !== '') {
            return;
        }

        setting([
            'google_sheets_columns' => json_encode(self::defaultEnabledKeys()),
        ]);
    }
}
