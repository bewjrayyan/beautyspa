<?php

namespace Modules\Loyalty\Services;

use Modules\Loyalty\Enums\TransactionType;
use Modules\Review\Entities\Review;
use Modules\User\Entities\User;

class LoyaltyReviewRewardService
{
    public function __construct(
        private LoyaltyConfig $config,
        private LoyaltyWalletService $wallets
    ) {}


    public function award(User $user, Review $review): int
    {
        $points = $this->config->reviewRewardPoints();

        if ($points <= 0) {
            return 0;
        }

        $wallet = $this->wallets->getOrCreateForUser($user);
        $referenceId = $user->id . ':' . $review->product_id;

        if ($this->wallets->findExistingTransaction(
            $wallet,
            TransactionType::BONUS,
            'review',
            $referenceId
        )) {
            return 0;
        }

        $this->wallets->credit(
            $wallet,
            $points,
            TransactionType::BONUS,
            'review',
            $referenceId,
            trans('loyalty::messages.review_bonus'),
            [
                'review_id' => $review->id,
                'product_id' => $review->product_id,
            ],
            now()->addMonths($this->config->pointsExpireMonths())
        );

        return $points;
    }
}
