<?php

namespace Modules\Payment\Responses;

use Modules\Order\Entities\Order;
use Modules\Payment\GatewayResponse;
use Modules\Payment\HasTransactionReference;
use Modules\Payment\ShouldRedirect;

class ChipResponse extends GatewayResponse implements ShouldRedirect, HasTransactionReference
{
    public function __construct(
        private readonly Order $order,
        private readonly array $purchase,
    ) {
    }


    public function getOrderId(): int
    {
        return $this->order->id;
    }


    public function getRedirectUrl(): string
    {
        return $this->purchase['checkout_url'];
    }


    public function getTransactionReference(): string
    {
        return (string) ($this->purchase['id'] ?? request('id', ''));
    }
}
