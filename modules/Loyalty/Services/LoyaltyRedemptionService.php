<?php

namespace Modules\Loyalty\Services;

use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Loyalty\Entities\LoyaltyRedemptionHold;

class LoyaltyRedemptionService
{
    public function __construct(private LoyaltyConfig $config) {}


    public function maxRedeemablePoints(LoyaltyWallet $wallet, float $cartSubtotalRm): int
    {
        $maxByPercent = $this->config->rmToPoints(
            $cartSubtotalRm * ($this->config->maxRedeemPercent() / 100)
        );

        return min($wallet->balance, $maxByPercent);
    }


    public function quote(LoyaltyWallet $wallet, float $cartSubtotalRm, ?int $requestedPoints = null): array
    {
        $maxPoints = $this->maxRedeemablePoints($wallet, $cartSubtotalRm);
        $points = $requestedPoints === null
            ? $maxPoints
            : min(max(0, $requestedPoints), $maxPoints);

        $discountRm = $this->config->pointsToRm($points);

        return [
            'points' => $points,
            'discount_rm' => $discountRm,
            'max_points' => $maxPoints,
            'point_value_rm' => $this->config->pointValueRm(),
        ];
    }


    public function createHold(
        LoyaltyWallet $wallet,
        string $cartId,
        int $points,
        float $discountAmount
    ): LoyaltyRedemptionHold {
        LoyaltyRedemptionHold::where('user_id', $wallet->user_id)
            ->where('cart_id', $cartId)
            ->delete();

        return LoyaltyRedemptionHold::create([
            'user_id' => $wallet->user_id,
            'cart_id' => $cartId,
            'points' => $points,
            'discount_amount' => $discountAmount,
            'expires_at' => now()->addMinutes($this->config->holdMinutes()),
        ]);
    }


    public function releaseHold(int $userId, string $cartId): void
    {
        LoyaltyRedemptionHold::where('user_id', $userId)
            ->where('cart_id', $cartId)
            ->delete();
    }
}
