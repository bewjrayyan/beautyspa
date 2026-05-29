<?php

namespace Modules\Payment\Services;

class ChipPaymentMethodConfig
{
    public const METHOD_ALL = 'chip';

    public const METHOD_FPX = 'chip_fpx';

    public const METHOD_CARD = 'chip_card';

    public const METHOD_ATOME = 'chip_atome';

    /**
     * @return array<string, array{
     *     label_setting: string,
     *     description_setting: string,
     *     enabled_setting: string,
     *     surcharge_setting: string,
     *     whitelist_setting: string,
     *     default_whitelist: string[],
     *     default_surcharge: int,
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
                'whitelist_setting' => 'chip_fpx_whitelist',
                'default_whitelist' => ['fpx'],
                'default_surcharge' => 0,
            ],
            self::METHOD_CARD => [
                'label_setting' => 'chip_card_label',
                'description_setting' => 'chip_card_description',
                'enabled_setting' => 'chip_card_enabled',
                'surcharge_setting' => 'chip_card_surcharge',
                'whitelist_setting' => 'chip_card_whitelist',
                'default_whitelist' => ['card'],
                'default_surcharge' => 0,
            ],
            self::METHOD_ATOME => [
                'label_setting' => 'chip_atome_label',
                'description_setting' => 'chip_atome_description',
                'enabled_setting' => 'chip_atome_enabled',
                'surcharge_setting' => 'chip_atome_surcharge',
                'whitelist_setting' => 'chip_atome_whitelist',
                'default_whitelist' => ['atome'],
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

        return $paymentMethod === self::METHOD_ALL
            || isset(self::methods()[$paymentMethod]);
    }

    public static function configFor(string $gatewayKey): ?array
    {
        if ($gatewayKey === self::METHOD_ALL) {
            return null;
        }

        return self::methods()[$gatewayKey] ?? null;
    }
}
