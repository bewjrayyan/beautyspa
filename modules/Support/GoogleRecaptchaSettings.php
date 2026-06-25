<?php

namespace Modules\Support;

class GoogleRecaptchaSettings
{
    public static function enabled(): bool
    {
        return (bool) setting('google_recaptcha_enabled', false);
    }


    public static function isV3(): bool
    {
        return self::enabled()
            && setting('google_recaptcha_type', 'v2') === 'v3';
    }


    public static function isV2(): bool
    {
        return self::enabled() && ! self::isV3();
    }


    public static function siteKey(): string
    {
        return trim((string) setting('google_recaptcha_site_key', ''));
    }


    public static function secretKey(): string
    {
        return trim((string) setting('google_recaptcha_secret_key', ''));
    }


    public static function v3ScoreThreshold(): float
    {
        $threshold = (float) setting('google_recaptcha_v3_score_threshold', 0.5);

        return max(0.0, min(1.0, $threshold));
    }
}
