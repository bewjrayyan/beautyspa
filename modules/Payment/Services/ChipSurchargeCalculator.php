<?php

namespace Modules\Payment\Services;

use Modules\Order\Entities\Order;

class ChipSurchargeCalculator
{
    public function forGateway(string $gatewayKey, ?Order $order = null, ?int $amountSubunit = null): int
    {
        $config = ChipPaymentMethodConfig::configFor($gatewayKey);

        if ($config !== null) {
            return $this->flatSubunit($gatewayKey);
        }

        return $this->maxEnabledMethodSurcharge();
    }


    private function flatSubunit(string $gatewayKey): int
    {
        $config = ChipPaymentMethodConfig::configFor($gatewayKey);

        if ($config === null) {
            return 0;
        }

        $configured = setting($config['surcharge_setting']);

        if ($configured === null || $configured === '') {
            return max(0, (int) $config['default_surcharge']);
        }

        return max(0, (int) $configured);
    }


    private function maxEnabledMethodSurcharge(): int
    {
        $max = 0;

        foreach (ChipPaymentMethodConfig::checkoutMethodKeys() as $methodKey) {
            if (! setting($methodKey . '_enabled')) {
                continue;
            }

            $max = max($max, $this->flatSubunit($methodKey));
        }

        return $max;
    }
}
