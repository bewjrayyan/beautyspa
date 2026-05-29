<?php

namespace Modules\Payment\Responses;

use Modules\Order\Entities\Order;
use Modules\Payment\GatewayResponse;
use Modules\Payment\HasTransactionReference;

class VerifiedPaymentResponse extends GatewayResponse implements HasTransactionReference
{
    public function __construct(
        private readonly Order $order,
        private readonly string $transactionReference,
    ) {
    }

    public function getOrderId(): int
    {
        return $this->order->id;
    }

    public function getTransactionReference(): string
    {
        return $this->transactionReference;
    }
}
