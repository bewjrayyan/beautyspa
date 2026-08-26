<?php

namespace Modules\GoogleIntegration\Support;

use Modules\Order\Entities\Order;

class GoogleSheetsStatusConfig
{
    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            Order::PENDING => trans('order::statuses.pending'),
            Order::PROCESSING => trans('order::statuses.processing'),
            Order::COMPLETED => trans('order::statuses.completed'),
            Order::CANCELED => trans('order::statuses.canceled'),
        ];
    }


    /**
     * @return array<string, array{enabled: bool, tab: string}>
     */
    public static function defaults(): array
    {
        return [
            Order::PENDING => ['enabled' => true, 'tab' => 'Pending Orders'],
            Order::PROCESSING => ['enabled' => true, 'tab' => 'Processing Orders'],
            Order::COMPLETED => ['enabled' => true, 'tab' => 'Completed Bookings'],
            Order::CANCELED => ['enabled' => false, 'tab' => 'Canceled Orders'],
        ];
    }


    public static function isStatusEnabled(string $status): bool
    {
        return (bool) self::configForStatus($status)['enabled'];
    }


    public static function tabForStatus(string $status): string
    {
        $tab = trim((string) self::configForStatus($status)['tab']);

        if ($tab !== '') {
            return $tab;
        }

        return self::defaults()[$status]['tab'] ?? 'Orders';
    }


    /**
     * @return array<int, string>
     */
    public static function enabledStatuses(): array
    {
        return array_values(array_filter(
            array_keys(self::defaults()),
            fn (string $status) => self::isStatusEnabled($status),
        ));
    }


    /**
     * @return array{enabled: bool, tab: string}
     */
    public static function configForStatus(string $status): array
    {
        $defaults = self::defaults()[$status] ?? ['enabled' => false, 'tab' => 'Orders'];

        $enabled = setting(self::enabledKey($status), $defaults['enabled']);
        $tab = trim((string) setting(self::tabKey($status), ''));

        if ($status === Order::COMPLETED && $tab === '') {
            $legacy = trim((string) setting('google_sheet_name', ''));

            if ($legacy !== '') {
                $tab = $legacy;
            }
        }

        if ($tab === '') {
            $tab = $defaults['tab'];
        }

        return [
            'enabled' => filter_var($enabled, FILTER_VALIDATE_BOOLEAN),
            'tab' => $tab,
        ];
    }


    public static function enabledKey(string $status): string
    {
        return 'google_sheets_status_' . $status . '_enabled';
    }


    public static function tabKey(string $status): string
    {
        return 'google_sheets_status_' . $status . '_tab';
    }


    /**
     * @return array<int, string>
     */
    public static function settingKeys(): array
    {
        $keys = [];

        foreach (array_keys(self::defaults()) as $status) {
            $keys[] = self::enabledKey($status);
            $keys[] = self::tabKey($status);
        }

        return $keys;
    }


    /**
     * Seed missing Google Sheets status settings.
     * Safe during boot: never throws when MySQL is unavailable.
     *
     * @return array<int, string>
     */
    public static function applyMissingOnly(): array
    {
        $applied = [];

        try {
            $existing = setting()->all();
        } catch (\Throwable) {
            // Cannot read settings (e.g. DB down) — do not attempt writes.
            return [];
        }

        foreach (self::defaults() as $status => $config) {
            foreach ([self::enabledKey($status) => $config['enabled'], self::tabKey($status) => $config['tab']] as $key => $value) {
                if (array_key_exists($key, $existing)) {
                    continue;
                }

                try {
                    // updateOrCreate issues SELECT WHERE key=… — skip entirely if DB is down.
                    if (! self::databaseReachable()) {
                        return $applied;
                    }

                    setting([$key => $value]);
                    $applied[] = $key;
                } catch (\Throwable) {
                    return $applied;
                }
            }
        }

        return $applied;
    }


    private static function databaseReachable(): bool
    {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
