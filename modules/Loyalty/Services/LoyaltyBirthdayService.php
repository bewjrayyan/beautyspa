<?php

namespace Modules\Loyalty\Services;

use Modules\User\Entities\User;
use Modules\Loyalty\Enums\TransactionType;
use Illuminate\Database\Eloquent\Collection;

class LoyaltyBirthdayService
{
    public function __construct(
        private LoyaltyConfig $config,
        private LoyaltyWalletService $wallets,
        private LoyaltyNotificationService $notifications
    ) {}


    public function usersWithBirthdayToday(): Collection
    {
        return User::query()
            ->whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', now()->month)
            ->whereDay('date_of_birth', now()->day)
            ->get();
    }


    public function awardIfEligible(User $user): bool
    {
        if (!$this->config->birthdayBonusEnabled() || !$user->date_of_birth) {
            return false;
        }

        $dob = $user->date_of_birth;

        if ($dob->month !== now()->month || $dob->day !== now()->day) {
            return false;
        }

        $wallet = $this->wallets->getOrCreateForUser($user);
        $points = $this->config->birthdayBonusPoints();
        $referenceId = now()->year . ':birthday';

        if ($this->wallets->findExistingTransaction(
            $wallet,
            TransactionType::BONUS,
            'birthday',
            $referenceId
        )) {
            return false;
        }

        $this->wallets->credit(
            $wallet,
            $points,
            TransactionType::BONUS,
            'birthday',
            $referenceId,
            trans('loyalty::messages.birthday_bonus'),
            ['year' => now()->year]
        );

        $this->notifications->notifyBirthdayBonus($user, $points);

        return true;
    }
}
