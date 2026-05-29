<?php

namespace Modules\Loyalty\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\User;
use Modules\Loyalty\Services\LoyaltyReferralService;

class SyncReferralCodesCommand extends Command
{
    protected $signature = 'loyalty:sync-referral-codes';

    protected $description = 'Generate referral codes for users that do not have one.';


    public function handle(LoyaltyReferralService $referrals): int
    {
        $count = 0;

        User::whereNull('referral_code')->each(function (User $user) use ($referrals, &$count) {
            $referrals->ensureReferralCode($user);
            $count++;
        });

        $this->info("Generated {$count} referral code(s).");

        return self::SUCCESS;
    }
}
