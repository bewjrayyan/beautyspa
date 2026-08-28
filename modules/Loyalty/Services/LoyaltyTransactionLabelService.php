<?php

namespace Modules\Loyalty\Services;

use Modules\Loyalty\Entities\LoyaltyTransaction;
use Modules\Loyalty\Enums\TransactionType;

class LoyaltyTransactionLabelService
{
    public function customerLabel(LoyaltyTransaction $transaction): string
    {
        return match ($transaction->type) {
            TransactionType::EARN => $this->earnLabel($transaction),
            TransactionType::BONUS => $this->bonusLabel($transaction),
            TransactionType::REDEEM => $this->redeemLabel($transaction),
            TransactionType::CLAWBACK => $this->clawbackLabel($transaction),
            TransactionType::EXPIRE => trans('loyalty::account.tx_points_expired'),
            TransactionType::ADJUST => $transaction->description ?: trans('loyalty::reports.types.adjust'),
            default => $transaction->description ?: trans('loyalty::reports.types.' . $transaction->type),
        };
    }


    public function customerHint(LoyaltyTransaction $transaction): ?string
    {
        if ($transaction->type !== TransactionType::EARN || $transaction->reference_type !== 'order') {
            return null;
        }

        return trans('loyalty::account.tx_earn_purchase_hint');
    }


    private function earnLabel(LoyaltyTransaction $transaction): string
    {
        $orderId = (int) ($transaction->meta['order_id'] ?? 0);
        $eligible = isset($transaction->meta['eligible_rm'])
            ? (float) $transaction->meta['eligible_rm']
            : null;

        if ($orderId > 0 && $eligible !== null) {
            return trans('loyalty::account.tx_earn_purchase', [
                'id' => $orderId,
                'amount' => number_format($eligible, 2),
            ]);
        }

        if ($orderId > 0) {
            return trans('loyalty::messages.earn_from_order', ['id' => $orderId]);
        }

        return $transaction->description ?: trans('loyalty::reports.types.earn');
    }


    private function bonusLabel(LoyaltyTransaction $transaction): string
    {
        return match ($transaction->reference_type) {
            'referral' => trans('loyalty::account.tx_referral_bonus'),
            'birthday' => trans('loyalty::messages.birthday_bonus'),
            'review' => trans('loyalty::account.tx_review_bonus'),
            default => $transaction->description ?: trans('loyalty::reports.types.bonus'),
        };
    }


    private function redeemLabel(LoyaltyTransaction $transaction): string
    {
        $orderId = (int) ($transaction->meta['order_id'] ?? 0);

        if ($orderId > 0) {
            return trans('loyalty::messages.redeem_for_order', ['id' => $orderId]);
        }

        return $transaction->description ?: trans('loyalty::reports.types.redeem');
    }


    private function clawbackLabel(LoyaltyTransaction $transaction): string
    {
        $orderId = (int) ($transaction->meta['order_id'] ?? 0);

        if ($orderId > 0) {
            return trans('loyalty::messages.clawback_from_order', ['id' => $orderId]);
        }

        return $transaction->description ?: trans('loyalty::reports.types.clawback');
    }
}
