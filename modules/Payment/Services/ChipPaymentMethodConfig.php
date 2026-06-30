<?php

namespace Modules\Payment\Services;

class ChipPaymentMethodConfig
{
    public const METHOD_ALL = 'chip';

    public const METHOD_FPX = 'chip_fpx';

    public const METHOD_CARD = 'chip_card';

    public const METHOD_ATOME = 'chip_atome';

    public const METHOD_EWALLET = 'chip_ewallet';

    public const METHOD_DUITNOW = 'chip_duitnow';

    /**
     * Legacy CHIP whitelist codes that may still be posted from older checkout clients.
     *
     * @var array<string, string>
     */
    private const LEGACY_ALIASES = [
        'fpx' => self::METHOD_FPX,
        'card' => self::METHOD_CARD,
        'atome' => self::METHOD_ATOME,
        'ewallet' => self::METHOD_EWALLET,
        'duitnow' => self::METHOD_DUITNOW,
        'duitnow_qr' => self::METHOD_DUITNOW,
    ];

    public static function normalizeGatewayKey(string $paymentMethod): string
    {
        $paymentMethod = strtolower(trim($paymentMethod));

        return self::LEGACY_ALIASES[$paymentMethod] ?? $paymentMethod;
    }

    /**
     * @return array<string, array{
     *     label_setting: string,
     *     description_setting: string,
     *     enabled_setting: string,
     *     surcharge_setting: string,
     *     surcharge_type: 'flat'|'percent',
     *     percent_setting?: string,
     *     whitelist_setting: string,
     *     default_whitelist: string[],
     *     default_surcharge: int,
     *     default_surcharge_percent?: float,
     * }>
     */
    public static function methods(): array
    {
        return [
            self::METHOD_FPX => [
                'label_setting' => 'chip_fpx_label',
                'description_setting' => 'chip_fpx_description',
                'enabled_setting' => 'chip_fpx_enabled',
                'surcharge_setting' => 'chip_fpx_surcharge',
                'surcharge_type' => 'flat',
                'whitelist_setting' => 'chip_fpx_whitelist',
                'default_whitelist' => ['fpx'],
                'default_surcharge' => 100,
            ],
            self::METHOD_CARD => [
                'label_setting' => 'chip_card_label',
                'description_setting' => 'chip_card_description',
                'enabled_setting' => 'chip_card_enabled',
                'surcharge_setting' => 'chip_card_surcharge',
                'surcharge_type' => 'percent',
                'percent_setting' => 'chip_card_surcharge_percent',
                'whitelist_setting' => 'chip_card_whitelist',
                'default_whitelist' => ['card'],
                'default_surcharge' => 0,
                'default_surcharge_percent' => 2.0,
            ],
            self::METHOD_ATOME => [
                'label_setting' => 'chip_atome_label',
                'description_setting' => 'chip_atome_description',
                'enabled_setting' => 'chip_atome_enabled',
                'surcharge_setting' => 'chip_atome_surcharge',
                'surcharge_type' => 'percent',
                'percent_setting' => 'chip_atome_surcharge_percent',
                'whitelist_setting' => 'chip_atome_whitelist',
                'default_whitelist' => ['razer_atome'],
                'default_surcharge' => 0,
                'default_surcharge_percent' => 5.3,
            ],
            self::METHOD_EWALLET => [
                'label_setting' => 'chip_ewallet_label',
                'description_setting' => 'chip_ewallet_description',
                'enabled_setting' => 'chip_ewallet_enabled',
                'surcharge_setting' => 'chip_ewallet_surcharge',
                'surcharge_type' => 'percent',
                'percent_setting' => 'chip_ewallet_surcharge_percent',
                'whitelist_setting' => 'chip_ewallet_whitelist',
                'default_whitelist' => ['razer_tng', 'razer_grabpay', 'razer_shopeepay'],
                'default_surcharge' => 0,
                'default_surcharge_percent' => 1.5,
            ],
            self::METHOD_DUITNOW => [
                'label_setting' => 'chip_duitnow_label',
                'description_setting' => 'chip_duitnow_description',
                'enabled_setting' => 'chip_duitnow_enabled',
                'surcharge_setting' => 'chip_duitnow_surcharge',
                'surcharge_type' => 'flat',
                'whitelist_setting' => 'chip_duitnow_whitelist',
                'default_whitelist' => ['duitnow_qr'],
                'default_surcharge' => 0,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function checkoutMethodKeys(): array
    {
        return array_keys(self::methods());
    }

    public static function isChipPaymentMethod(?string $paymentMethod): bool
    {
        if (! is_string($paymentMethod) || $paymentMethod === '') {
            return false;
        }

        $paymentMethod = self::normalizeGatewayKey($paymentMethod);

        return $paymentMethod === self::METHOD_ALL
            || isset(self::methods()[$paymentMethod]);
    }

    public static function configFor(string $gatewayKey): ?array
    {
        $gatewayKey = self::normalizeGatewayKey($gatewayKey);

        if ($gatewayKey === self::METHOD_ALL) {
            return null;
        }

        return self::methods()[$gatewayKey] ?? null;
    }
}
