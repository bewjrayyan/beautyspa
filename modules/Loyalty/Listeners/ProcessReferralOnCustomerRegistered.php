<?php

namespace Modules\Loyalty\Listeners;

use Modules\User\Events\CustomerRegistered;
use Modules\Loyalty\Services\LoyaltyReferralService;

class ProcessReferralOnCustomerRegistered
{
    public function __construct(private LoyaltyReferralService $referrals) {}


    public function handle(CustomerRegistered $event): void
    {
        $this->referrals->processRegistration(
            $event->user,
            $event->referralCode ?? request('referral_code')
        );
    }
}
