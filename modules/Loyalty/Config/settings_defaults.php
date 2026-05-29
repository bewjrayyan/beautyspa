<?php

/**
 * Default values for Settings → Loyalty (stored as loyalty_* keys).
 * Aligns with modules/Loyalty/Config/config.php and LOYALTY_MEMBERSHIP.md.
 */
return [
    'loyalty_earn_rate_per_rm' => (float) env('LOYALTY_EARN_RATE_PER_RM', 1),
    'loyalty_point_value_rm' => (float) env('LOYALTY_POINT_VALUE_RM', 0.10),
    'loyalty_max_redeem_percent' => (float) env('LOYALTY_MAX_REDEEM_PERCENT', 30),
    'loyalty_points_expire_months' => (int) env('LOYALTY_POINTS_EXPIRE_MONTHS', 12),
    'loyalty_hold_minutes' => (int) env('LOYALTY_HOLD_MINUTES', 15),
    'loyalty_allow_with_coupon' => filter_var(env('LOYALTY_ALLOW_WITH_COUPON', true), FILTER_VALIDATE_BOOLEAN),

    'loyalty_birthday_bonus_enabled' => filter_var(env('LOYALTY_BIRTHDAY_BONUS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'loyalty_birthday_bonus_points' => (int) env('LOYALTY_BIRTHDAY_BONUS_POINTS', 100),
    'loyalty_referral_enabled' => filter_var(env('LOYALTY_REFERRAL_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'loyalty_referral_bonus_referrer' => (int) env('LOYALTY_REFERRAL_BONUS_REFERRER', 50),
    'loyalty_referral_bonus_referee' => (int) env('LOYALTY_REFERRAL_BONUS_REFEREE', 25),
    'loyalty_expiring_notify_days' => (int) env('LOYALTY_EXPIRING_NOTIFY_DAYS', 14),

    'loyalty_whatsapp_tier_upgrade' => filter_var(env('LOYALTY_WHATSAPP_TIER_UPGRADE', true), FILTER_VALIDATE_BOOLEAN),
    'loyalty_whatsapp_points_earned' => filter_var(env('LOYALTY_WHATSAPP_POINTS_EARNED', true), FILTER_VALIDATE_BOOLEAN),
    'loyalty_whatsapp_points_expiring' => filter_var(env('LOYALTY_WHATSAPP_POINTS_EXPIRING', true), FILTER_VALIDATE_BOOLEAN),
    'loyalty_whatsapp_birthday_bonus' => filter_var(env('LOYALTY_WHATSAPP_BIRTHDAY_BONUS', true), FILTER_VALIDATE_BOOLEAN),
    'loyalty_whatsapp_referral_bonus' => filter_var(env('LOYALTY_WHATSAPP_REFERRAL_BONUS', true), FILTER_VALIDATE_BOOLEAN),
];
