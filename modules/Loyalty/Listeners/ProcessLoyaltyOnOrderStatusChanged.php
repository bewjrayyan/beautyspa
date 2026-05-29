<?php

namespace Modules\Loyalty\Listeners;

use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Loyalty\Services\LoyaltyEarnService;
use Modules\Loyalty\Services\LoyaltyOrderService;

class ProcessLoyaltyOnOrderStatusChanged
{
    public function __construct(
        private LoyaltyEarnService $earn,
        private LoyaltyOrderService $orders
    ) {}


    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        if ($order->status === Order::COMPLETED) {
            $this->earn->earnFromCompletedOrder($order);

            return;
        }

        if (in_array($order->status, [Order::CANCELED, Order::REFUNDED], true)) {
            $this->earn->clawbackFromOrder($order);
            $this->orders->refundRedemption($order);
        }
    }
}
