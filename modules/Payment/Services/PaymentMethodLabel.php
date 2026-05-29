<?php

namespace Modules\Payment\Services;

use Modules\Payment\Facades\Gateway;

class PaymentMethodLabel
{
    public static function resolve(?string $paymentMethod): string
    {
        if ($paymentMethod === null || $paymentMethod === '') {
            return '';
        }

        $gateway = Gateway::get($paymentMethod);

        if ($gateway !== null && filled($gateway->label ?? null)) {
            return (string) $gateway->label;
        }

        if (ChipPaymentMethodConfig::isChipPaymentMethod($paymentMethod)) {
            return self::resolveChipLabel($paymentMethod);
        }

        return ucwords(str_replace(['_', '-'], ' ', $paymentMethod));
    }

    private static function resolveChipLabel(string $paymentMethod): string
    {
        if ($paymentMethod === ChipPaymentMethodConfig::METHOD_ALL) {
            $label = trim((string) setting('chip_label'));

            return $label !== '' ? $label : 'CHIP';
        }

        $config = ChipPaymentMethodConfig::configFor($paymentMethod);

        if ($config !== null) {
            $label = trim((string) setting($config['label_setting']));

            if ($label !== '') {
                return $label;
            }
        }

        return 'CHIP';
    }
}
