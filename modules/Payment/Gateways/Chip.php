<?php

namespace Modules\Payment\Gateways;

use Modules\Payment\Services\ChipPaymentMethodConfig;

/**
 * @deprecated Use ChipGateway directly. Kept for backward compatibility.
 */
class Chip extends ChipGateway
{
    public function __construct()
    {
        parent::__construct(ChipPaymentMethodConfig::METHOD_ALL);
    }
}
