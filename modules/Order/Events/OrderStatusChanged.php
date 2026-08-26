<?php

namespace Modules\Order\Events;

use Modules\Order\Entities\Order;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use SerializesModels;

    /**
     * The instance of order.
     *
     * @var Order
     */
    public $order;

    /**
     * Which workflow status changed: order|payment|treatment|order_and_payment
     */
    public string $changeType;

    public ?string $previousValue;

    public ?string $newValue;


    /**
     * @param  'order'|'payment'|'treatment'|'order_and_payment'  $changeType
     */
    public function __construct(
        Order $order,
        string $changeType = 'order',
        ?string $previousValue = null,
        ?string $newValue = null,
    ) {
        $this->order = $order;
        $this->changeType = $changeType;
        $this->previousValue = $previousValue;
        $this->newValue = $newValue;
    }
}
