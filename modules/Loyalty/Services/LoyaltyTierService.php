<?php

namespace Modules\Loyalty\Services;

use Modules\Loyalty\Entities\LoyaltyTier;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Loyalty\Entities\LoyaltyTierHistory;

class LoyaltyTierService
{
    public function evaluate(LoyaltyWallet $wallet, string $reason = 'spend_threshold'): ?array
    {
        $wallet->refresh();

        $qualified = LoyaltyTier::where('is_active', true)
            ->where('min_lifetime_spend', '<=', $wallet->lifetime_spend)
            ->orderByDesc('min_lifetime_spend')
            ->first();

        if (!$qualified || $qualified->id === $wallet->tier_id) {
            return null;
        }

        $fromTier = $wallet->tier;
        $fromTierId = $wallet->tier_id;

        $wallet->update([
            'tier_id' => $qualified->id,
            'tier_assigned_at' => now(),
        ]);

        LoyaltyTierHistory::create([
            'user_id' => $wallet->user_id,
            'from_tier_id' => $fromTierId,
            'to_tier_id' => $qualified->id,
            'reason' => $reason,
            'created_at' => now(),
        ]);

        return [
            'from' => $fromTier,
            'to' => $qualified,
        ];
    }
}
