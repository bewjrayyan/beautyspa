<?php

namespace Modules\Loyalty\Console;

use Illuminate\Console\Command;
use Modules\Loyalty\Services\LoyaltyBirthdayService;

class AwardBirthdayBonusCommand extends Command
{
    protected $signature = 'loyalty:award-birthday-bonus';

    protected $description = 'Award birthday loyalty bonus points to eligible customers.';


    public function handle(LoyaltyBirthdayService $birthdays): int
    {
        $awarded = 0;

        foreach ($birthdays->usersWithBirthdayToday() as $user) {
            if ($birthdays->awardIfEligible($user)) {
                $awarded++;
            }
        }

        $this->info("Awarded birthday bonus to {$awarded} customer(s).");

        return self::SUCCESS;
    }
}
