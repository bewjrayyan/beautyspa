<?php

namespace Modules\Loyalty\Services;

use Modules\Loyalty\Entities\LoyaltyStampProgram;
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
        $stampCards = $this->stamps->forAccount($user);
        $hasStampPrograms = LoyaltyStampProgram::query()->active()->exists();

        if ($pointsBalance <= 0 && $stampCards === [] && ! $hasStampPrograms) {
            return null;
        }

        return [
            'points_balance' => $pointsBalance,
            'points_worth_rm' => $pointsWorthRm,
            'stamp_cards' => $stampCards,
        ];
    }
}
