<?php

namespace Tests\Unit\Loyalty;

use Modules\Loyalty\Entities\LoyaltyTransaction;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Loyalty\Enums\TransactionType;
use Modules\Loyalty\Services\LoyaltyConfig;
use Modules\Loyalty\Services\LoyaltyReviewRewardService;
use Modules\Loyalty\Services\LoyaltyWalletService;
use Modules\Review\Entities\Review;
use Modules\User\Entities\User;
use PHPUnit\Framework\TestCase;

class LoyaltyReviewRewardServiceTest extends TestCase
{
    public function test_it_does_not_touch_a_wallet_when_review_rewards_are_disabled(): void
    {
        $config = $this->createMock(LoyaltyConfig::class);
        $config->method('reviewRewardPoints')->willReturn(0);

        $wallets = $this->createMock(LoyaltyWalletService::class);
        $wallets->expects($this->never())->method('getOrCreateForUser');

        $service = new LoyaltyReviewRewardService($config, $wallets);

        $this->assertSame(0, $service->award(new User(), new Review()));
    }


    public function test_it_does_not_credit_the_same_customer_and_product_twice(): void
    {
        $config = $this->createMock(LoyaltyConfig::class);
        $config->method('reviewRewardPoints')->willReturn(20);

        $user = new User();
        $user->id = 7;

        $review = new Review();
        $review->product_id = 31;

        $wallet = new LoyaltyWallet();
        $wallet->id = 4;

        $wallets = $this->createMock(LoyaltyWalletService::class);
        $wallets->expects($this->once())
            ->method('getOrCreateForUser')
            ->with($user)
            ->willReturn($wallet);
        $wallets->expects($this->once())
            ->method('findExistingTransaction')
            ->with($wallet, TransactionType::BONUS, 'review', '7:31')
            ->willReturn(new LoyaltyTransaction());
        $wallets->expects($this->never())->method('credit');

        $service = new LoyaltyReviewRewardService($config, $wallets);

        $this->assertSame(0, $service->award($user, $review));
    }
}
