<?php

namespace Modules\Payment\Services;

use Modules\Payment\Facades\Gateway;
use Modules\Payment\GatewayInterface;
use Modules\Payment\Gateways\ChipGateway;

class PaymentGatewayResolver
{
    public static function get(string $name): ?GatewayInterface
    {
        $gateway = Gateway::get($name);

        if ($gateway !== null) {
            return $gateway;
        }

        if (ChipPaymentMethodConfig::isChipPaymentMethod($name) && setting('chip_enabled')) {
            return new ChipGateway($name);
        }

        return null;
    }
}
