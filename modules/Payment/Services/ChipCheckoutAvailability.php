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
            if (setting($methodKey . '_enabled')) {
                return false;
            }
        }

        return true;
    }
}
