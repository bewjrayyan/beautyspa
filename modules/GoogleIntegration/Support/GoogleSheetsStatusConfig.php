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
            Order::PENDING_PAYMENT => trans('order::statuses.pending_payment'),
            Order::PENDING => trans('order::statuses.pending'),
            Order::PROCESSING => trans('order::statuses.processing'),
            Order::ON_HOLD => trans('order::statuses.on_hold'),
            Order::COMPLETED => trans('order::statuses.completed'),
            Order::CANCELED => trans('order::statuses.canceled'),
            Order::REFUNDED => trans('order::statuses.refunded'),
        ];
    }


    /**
     * @return array<string, array{enabled: bool, tab: string}>
     */
    public static function defaults(): array
    {
        return [
            Order::PENDING_PAYMENT => ['enabled' => true, 'tab' => 'Pending Payment Orders'],
            Order::PENDING => ['enabled' => false, 'tab' => 'Pending Orders'],
            Order::PROCESSING => ['enabled' => true, 'tab' => 'Processing Orders'],
            Order::ON_HOLD => ['enabled' => false, 'tab' => 'On Hold Orders'],
            Order::COMPLETED => ['enabled' => true, 'tab' => 'Completed Bookings'],
            Order::CANCELED => ['enabled' => false, 'tab' => 'Canceled Orders'],
            Order::REFUNDED => ['enabled' => false, 'tab' => 'Refunded Orders'],
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

        foreach (self::defaults() as $status => $config) {
            foreach ([self::enabledKey($status) => $config['enabled'], self::tabKey($status) => $config['tab']] as $key => $value) {
                if (array_key_exists($key, $existing)) {
                    continue;
                }

                setting([$key => $value]);
                $applied[] = $key;
            }
        }

        return $applied;
    }
}
