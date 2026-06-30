<?php

namespace Modules\Payment\Services;

use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;

class ChipCheckoutLogo
{
    /**
     * @var array<string, array{setting: string, default: string}>
     */
    private const GATEWAYS = [
        ChipPaymentMethodConfig::METHOD_ALL => [
            'setting' => 'chip_checkout_logo',
            'default' => 'images/payments/online-banking.png',
        ],
        ChipPaymentMethodConfig::METHOD_FPX => [
            'setting' => 'chip_fpx_checkout_logo',
            'default' => 'images/payments/online-banking-fpx.png?v=1',
        ],
        ChipPaymentMethodConfig::METHOD_CARD => [
            'setting' => 'chip_card_checkout_logo',
            'default' => 'images/payments/card-international.png?v=2',
        ],
        ChipPaymentMethodConfig::METHOD_ATOME => [
            'setting' => 'chip_atome_checkout_logo',
            'default' => 'images/payments/atome-chip-part.png?v=3',
        ],
        ChipPaymentMethodConfig::METHOD_EWALLET => [
            'setting' => 'chip_ewallet_checkout_logo',
            'default' => 'images/payments/pay-with-chip-all.png?v=1',
        ],
        ChipPaymentMethodConfig::METHOD_DUITNOW => [
            'setting' => 'chip_duitnow_checkout_logo',
            'default' => 'images/payments/online-banking.png?v=1',
        ],
    ];

    /**
     * @return list<string>
     */
    public static function settingKeys(): array
    {
        return array_values(array_map(
            static fn (array $config): string => $config['setting'],
            self::GATEWAYS
        ));
    }

    /**
     * @return array<string, File>
     */
    public static function adminFiles(): array
    {
        $files = [];

        foreach (self::GATEWAYS as $gatewayId => $config) {
            $files[$gatewayId] = File::findOrNew(setting($config['setting']));
        }

        return $files;
    }

    public static function settingKey(string $gatewayId): ?string
    {
        return self::GATEWAYS[$gatewayId]['setting'] ?? null;
    }

    public static function defaultUrl(string $gatewayId): string
    {
        $path = self::GATEWAYS[$gatewayId]['default'] ?? '';

        return $path !== '' ? asset($path) : '';
    }

    public static function url(string $gatewayId): string
    {
        $config = self::GATEWAYS[$gatewayId] ?? null;

        if ($config === null) {
            return '';
        }

        $fileId = (int) setting($config['setting']);

        if ($fileId > 0) {
            $file = File::find($fileId);

            if ($file?->path) {
                return $file->path;
            }
        }

        return self::defaultUrl($gatewayId);
    }

    public static function urlForOrder(Order $order): ?string
    {
        $gatewayKey = ChipPaymentMethodConfig::normalizeGatewayKey(
            (string) $order->getRawOriginal('payment_method')
        );

        if (! ChipPaymentMethodConfig::isChipPaymentMethod($gatewayKey)) {
            return null;
        }

        return self::url($gatewayKey);
    }
}
