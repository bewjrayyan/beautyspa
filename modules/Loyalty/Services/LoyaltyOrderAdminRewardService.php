<?php

namespace Modules\Loyalty\Services;

use Modules\Loyalty\Entities\LoyaltyTransaction;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Loyalty\Enums\TransactionType;
use Modules\Order\Entities\Order;

class LoyaltyOrderAdminRewardService
{
    public function __construct(
        private LoyaltyConfig $config,
        private LoyaltyEarnService $earn,
        private LoyaltyStampAdminService $stamps
    ) {}


    public function forOrder(Order $order): ?array
    {
        if (! $order->customer_id) {
            return null;
        }

        $wallet = LoyaltyWallet::query()
            ->with('tier')
            ->where('user_id', $order->customer_id)
            ->first();

        if (! $wallet) {
            return null;
        }

        $transactions = LoyaltyTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('reference_type', 'order')
            ->where('reference_id', 'like', $order->id . ':%')
            ->orderBy('created_at')
            ->get();

        $ledgerEarned = (int) $transactions
            ->where('type', TransactionType::EARN)
            ->where('points', '>', 0)
            ->sum('points');
        $ledgerRedeemed = abs((int) $transactions
            ->where('type', TransactionType::REDEEM)
            ->where('points', '<', 0)
            ->sum('points'));
        $recordedEarned = (int) $order->loyalty_points_earned;
        $recordedRedeemed = (int) $order->loyalty_points_redeemed;
        $pointsEarned = $ledgerEarned > 0 ? $ledgerEarned : $recordedEarned;
        $pointsRedeemed = $ledgerRedeemed > 0 ? $ledgerRedeemed : $recordedRedeemed;
        $tierMultiplier = (float) ($wallet->tier?->earn_multiplier ?? 1);

        if ($tierMultiplier <= 0) {
            $tierMultiplier = 1;
        }
        $eligibleAmount = $this->earn->eligibleAmount($order);
        $calculatedPoints = $this->earn->calculatePointsForOrder($order, $tierMultiplier);
        $stampData = $this->stamps->orderStampData($order);
        $stampCards = $stampData['stampCards'] ?? [];

        return [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'points_earned' => $pointsEarned,
            'points_redeemed' => $pointsRedeemed,
            'recorded_points_earned' => $recordedEarned,
            'recorded_points_redeemed' => $recordedRedeemed,
            'ledger_points_earned' => $ledgerEarned,
            'ledger_points_redeemed' => $ledgerRedeemed,
            'earned_record_mismatch' => $ledgerEarned > 0 && $ledgerEarned !== $recordedEarned,
            'redeemed_ledger_missing' => $recordedRedeemed > 0 && $ledgerRedeemed === 0,
            'redemption_value_rm' => $this->config->pointsToRm($pointsRedeemed),
            'wallet_value_rm' => $this->config->pointsToRm((int) $wallet->balance),
            'eligible_amount_rm' => $eligibleAmount,
            'earn_rate_per_rm' => $this->config->earnRatePerRm(),
            'tier_multiplier' => $tierMultiplier,
            'calculated_points' => $calculatedPoints,
            'allow_with_coupon' => $this->config->allowWithCoupon(),
            'payment_gateway_restricted' => false,
            'payment_method' => $order->payment_method,
            'stamp_cards' => $stampCards,
            'stamps_added' => (int) collect($stampCards)->sum('stamps_added_this_order'),
        ];
    }
}
