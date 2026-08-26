<?php

namespace Modules\Loyalty\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Loyalty\Services\LoyaltyEarnService;
use Modules\Loyalty\Services\LoyaltyOrderService;
use Modules\Loyalty\Services\LoyaltyStampAwardService;
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
        private LoyaltyOrderService $orders,
        private LoyaltyStampAwardService $stamps
    ) {}

    public function handle(OrderStatusChanged $event): void
    {
        $changeType = $event->changeType ?: 'order';

        // Loyalty reacts to order (and combined order+payment) transitions only.
        if (! in_array($changeType, ['order', 'order_and_payment'], true)) {
            return;
        }

        $order = $event->order;

        if ($order->status === Order::COMPLETED) {
            $this->earn->earnFromCompletedOrder($order);
            $this->stamps->awardForOrder($order);

            return;
        }

        if (in_array($order->status, [Order::CANCELED, Order::REFUNDED], true)) {
            $this->earn->clawbackFromOrder($order);
            $this->orders->refundRedemption($order);
            $this->stamps->clawbackForOrder($order);
        }
    }
}
