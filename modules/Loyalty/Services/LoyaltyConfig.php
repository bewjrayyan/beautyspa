<?php

namespace Modules\Loyalty\Services;

class LoyaltyConfig
{
    public function earnRatePerRm(): float
    {
        return (float) $this->get('earn_rate_per_rm', 1);
    }


    public function pointValueRm(): float
    {
        return (float) $this->get('point_value_rm', 0.10);
    }


    public function maxRedeemPercent(): float
    {
        return (float) $this->get('max_redeem_percent', 30);
    }


    public function pointsExpireMonths(): int
    {
        return (int) $this->get('points_expire_months', 12);
    }


    public function holdMinutes(): int
    {
        return (int) $this->get('hold_minutes', 15);
    }


    public function pointsToRm(int $points): float
    {
        return round($points * $this->pointValueRm(), 2);
    }


    public function rmToPoints(float $rm): int
    {
        if ($this->pointValueRm() <= 0) {
            return 0;
        }

        return (int) floor($rm / $this->pointValueRm());
    }


    public function expiringNotifyDays(): int
    {
        return (int) $this->get('expiring_notify_days', 14);
    }


    public function birthdayBonusEnabled(): bool
    {
        return $this->getBool('birthday_bonus_enabled', true);
    }


    public function birthdayBonusPoints(): int
    {
        return (int) $this->get('birthday_bonus_points', 100);
    }


    public function referralEnabled(): bool
    {
        return $this->getBool('referral_enabled', true);
    }


    public function referralBonusReferrer(): int
    {
        return (int) $this->get('referral_bonus_referrer', 50);
    }


    public function referralBonusReferee(): int
    {
        return (int) $this->get('referral_bonus_referee', 25);
    }


    public function whatsappTierUpgrade(): bool
    {
        return $this->getBool('whatsapp_tier_upgrade', true);
    }


    public function whatsappPointsEarned(): bool
    {
        return $this->getBool('whatsapp_points_earned', true);
    }


    public function whatsappPointsExpiring(): bool
    {
        return $this->getBool('whatsapp_points_expiring', true);
    }


    public function whatsappBirthdayBonus(): bool
    {
        return $this->getBool('whatsapp_birthday_bonus', true);
    }


    public function whatsappReferralBonus(): bool
    {
        return $this->getBool('whatsapp_referral_bonus', true);
    }


    public function allowWithCoupon(): bool
    {
        return $this->getBool('allow_with_coupon', true);
    }


    private function getBool(string $key, bool $default): bool
    {
        return filter_var($this->get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }


    private function get(string $key, mixed $default): mixed
    {
        $settingKey = 'loyalty_' . $key;

        if (function_exists('setting')) {
            $fromSetting = setting($settingKey);

            if ($fromSetting !== null && $fromSetting !== '') {
                return $fromSetting;
            }
        }

        return config("fleetcart.modules.loyalty.config.{$key}", $default);
    }
}
