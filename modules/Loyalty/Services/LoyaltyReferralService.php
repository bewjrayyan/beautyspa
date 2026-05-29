<?php

namespace Modules\Loyalty\Services;

use Illuminate\Support\Str;
use Modules\User\Entities\User;
use Modules\Loyalty\Enums\TransactionType;

class LoyaltyReferralService
{
    public function __construct(
        private LoyaltyConfig $config,
        private LoyaltyWalletService $wallets,
        private LoyaltyNotificationService $notifications
    ) {}


    public function ensureReferralCode(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        $user->update(['referral_code' => $code]);

        return $code;
    }


    public function findReferrerByCode(?string $code): ?User
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return null;
        }

        return User::where('referral_code', $code)->first();
    }


    public function processRegistration(User $newUser, ?string $referralCode): void
    {
        if (!$this->config->referralEnabled()) {
            $this->ensureReferralCode($newUser);

            return;
        }

        $this->ensureReferralCode($newUser);

        $referrer = $this->findReferrerByCode($referralCode);

        if (!$referrer || $referrer->id === $newUser->id) {
            return;
        }

        if ($newUser->referred_by_user_id) {
            return;
        }

        $newUser->update(['referred_by_user_id' => $referrer->id]);

        $refereePoints = $this->config->referralBonusReferee();
        $referrerPoints = $this->config->referralBonusReferrer();

        if ($refereePoints > 0) {
            $this->awardBonus(
                $newUser,
                $refereePoints,
                'referral',
                $newUser->id . ':referee',
                false
            );
        }

        if ($referrerPoints > 0) {
            $this->awardBonus(
                $referrer,
                $referrerPoints,
                'referral',
                $newUser->id . ':referrer:' . $referrer->id,
                true
            );
        }
    }


    private function awardBonus(
        User $user,
        int $points,
        string $referenceType,
        string $referenceId,
        bool $asReferrer
    ): void {
        $wallet = $this->wallets->getOrCreateForUser($user);

        if ($this->wallets->findExistingTransaction($wallet, TransactionType::BONUS, $referenceType, $referenceId)) {
            return;
        }

        $this->wallets->credit(
            $wallet,
            $points,
            TransactionType::BONUS,
            $referenceType,
            $referenceId,
            trans('loyalty::messages.referral_bonus'),
            ['user_id' => $user->id]
        );

        $this->notifications->notifyReferralBonus($user, $points, $asReferrer);
    }
}
