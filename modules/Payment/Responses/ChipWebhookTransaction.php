<?php

namespace Modules\Payment\Responses;

use Modules\Payment\HasTransactionReference;

class ChipWebhookTransaction implements HasTransactionReference
{
    public function __construct(private readonly string $purchaseId)
    {
    }


    public function getTransactionReference(): string
    {
        return $this->purchaseId;
    }
}
