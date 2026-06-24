<?php

namespace Modules\Order\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Order\Entities\Order;

class OrderCreated
{
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
    ) {
    }
}
