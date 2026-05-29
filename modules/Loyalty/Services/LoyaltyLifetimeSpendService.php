<?php

namespace Modules\Loyalty\Services;

use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Order\Entities\Order;
use Modules\User\Entities\User;

class LoyaltyLifetimeSpendService
{
    public function calculateForUser(User|int $user): float
    {
        $userId = $user instanceof User ? $user->id : $user;

        return (float) Order::query()
            ->where('customer_id', $userId)
            ->where('status', Order::COMPLETED)
            ->get()
            ->sum(fn (Order $order) => $this->eligibleAmount($order));
    }


    public function eligibleAmount(Order $order): float
    {
        $subTotal = (float) $order->sub_total->amount();
        $discount = (float) $order->discount->amount();
        $loyaltyDiscount = (float) ($order->loyalty_discount_amount ?? 0);

        return max(0, $subTotal - $discount - $loyaltyDiscount);
    }


    public function recalculateWallet(LoyaltyWallet $wallet): float
    {
        $total = $this->calculateForUser($wallet->user_id);

        $wallet->update(['lifetime_spend' => $total]);

        return $total;
    }
}
