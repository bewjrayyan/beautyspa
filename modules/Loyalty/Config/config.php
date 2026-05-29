<?php

return [
    'earn_rate_per_rm' => (float) env('LOYALTY_EARN_RATE_PER_RM', 1),
    'point_value_rm' => (float) env('LOYALTY_POINT_VALUE_RM', 0.10),
    'max_redeem_percent' => (float) env('LOYALTY_MAX_REDEEM_PERCENT', 30),
    'points_expire_months' => (int) env('LOYALTY_POINTS_EXPIRE_MONTHS', 12),
    'hold_minutes' => (int) env('LOYALTY_HOLD_MINUTES', 15),
    'default_tier_slug' => 'silver',
    'expiring_notify_days' => 14,
    'birthday_bonus_enabled' => true,
    'birthday_bonus_points' => 100,
    'referral_enabled' => true,
    'referral_bonus_referrer' => 50,
    'referral_bonus_referee' => 25,
    'whatsapp_tier_upgrade' => true,
    'whatsapp_points_earned' => true,
    'whatsapp_points_expiring' => true,
    'whatsapp_birthday_bonus' => true,
    'whatsapp_referral_bonus' => true,
    'allow_with_coupon' => true,
];
