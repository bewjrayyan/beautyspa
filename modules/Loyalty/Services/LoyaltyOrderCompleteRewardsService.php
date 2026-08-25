<?php

namespace Modules\Loyalty\Services;

use Modules\Order\Entities\Order;
use Modules\User\Entities\User;

class LoyaltyOrderCompleteRewardsService
{
    public function __construct(
        private LoyaltyWalletService $wallets,
        private LoyaltyConfig $config,
        private LoyaltyStampProgressService $stamps
    ) {}


    public function forOrder(Order $order): ?array
    {
        if (! $order->customer_id) {
            return null;
        }

        $user = User::find($order->customer_id);

        if (! $user) {
            return null;
        }

        $wallet = $this->wallets->getOrCreateForUser($user);
        $pointsBalance = (int) $wallet->balance;
        $pointsWorthRm = $this->config->pointsToRm($pointsBalance);
        // Celebration / order views: only active progress or redeemable cards — never expired.
        $stampCards = $this->stamps->forOrderComplete($user);

        if ($pointsBalance <= 0 && $stampCards === []) {
            return null;
        }

        return [
            'points_balance' => $pointsBalance,
            'points_worth_rm' => $pointsWorthRm,
            'stamp_cards' => $stampCards,
        ];
    }
}
