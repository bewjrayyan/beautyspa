<?php

namespace Modules\Loyalty\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Loyalty\Services\LoyaltyEarnService;
use Modules\Loyalty\Services\LoyaltyOrderService;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;

class ProcessLoyaltyOnOrderStatusChanged implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [15, 60, 180];

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
