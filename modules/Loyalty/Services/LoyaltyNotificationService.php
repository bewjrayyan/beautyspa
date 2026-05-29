<?php

namespace Modules\Loyalty\Services;

use Modules\Sms\Sms;
use Modules\User\Entities\User;
use Modules\Loyalty\Entities\LoyaltyTier;
use Modules\Sms\Exceptions\SmsException;
use Modules\Loyalty\Entities\LoyaltyWallet;

class LoyaltyNotificationService
{
    public function __construct(private LoyaltyConfig $config) {}


    public function notifyPointsEarned(User $user, int $points, LoyaltyWallet $wallet): void
    {
        if (!$this->config->whatsappPointsEarned() || !$user->phone) {
            return;
        }

        $this->send($user->phone, trans('loyalty::notifications.points_earned', [
            'first_name' => $user->first_name,
            'points' => number_format($points),
            'balance' => number_format($wallet->balance),
            'tier' => $wallet->tier?->name ?? '',
        ]));
    }


    public function notifyTierUpgraded(User $user, LoyaltyTier $fromTier, LoyaltyTier $toTier): void
    {
        if (!$this->config->whatsappTierUpgrade() || !$user->phone) {
            return;
        }

        $this->send($user->phone, trans('loyalty::notifications.tier_upgraded', [
            'first_name' => $user->first_name,
            'from' => $fromTier->name,
            'to' => $toTier->name,
        ]));
    }


    public function notifyPointsExpiring(User $user, int $points, int $daysLeft): void
    {
        if (!$this->config->whatsappPointsExpiring() || !$user->phone) {
            return;
        }

        $this->send($user->phone, trans('loyalty::notifications.points_expiring', [
            'first_name' => $user->first_name,
            'points' => number_format($points),
            'days' => $daysLeft,
        ]));
    }


    public function notifyBirthdayBonus(User $user, int $points): void
    {
        if (!$this->config->whatsappBirthdayBonus() || !$user->phone) {
            return;
        }

        $this->send($user->phone, trans('loyalty::notifications.birthday_bonus', [
            'first_name' => $user->first_name,
            'points' => number_format($points),
        ]));
    }


    public function notifyReferralBonus(User $user, int $points, bool $asReferrer): void
    {
        if (!$this->config->whatsappReferralBonus() || !$user->phone) {
            return;
        }

        $key = $asReferrer ? 'referral_bonus_referrer' : 'referral_bonus_referee';

        $this->send($user->phone, trans("loyalty::notifications.{$key}", [
            'first_name' => $user->first_name,
            'points' => number_format($points),
        ]));
    }


    private function send(string $phone, string $message): void
    {
        try {
            Sms::send($phone, $message);
        } catch (SmsException) {
            //
        }
    }
}
