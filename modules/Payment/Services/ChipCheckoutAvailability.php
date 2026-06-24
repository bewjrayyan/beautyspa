<?php

namespace Modules\Payment\Services;

class ChipCheckoutAvailability
{
    /**
     * Whether the generic CHIP gateway (no whitelist) should appear at checkout.
     */
    public static function showAllMethodsGateway(): bool
    {
        if (setting('chip_all_methods_enabled')) {
            return true;
        }

        foreach (ChipPaymentMethodConfig::checkoutMethodKeys() as $methodKey) {
            $config = ChipPaymentMethodConfig::configFor($methodKey);

            if ($config !== null && setting($config['enabled_setting'])) {
                return false;
            }
        }

        return true;
    }
}
