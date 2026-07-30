<?php

namespace Modules\Setting\Support;

use Illuminate\Support\Facades\Crypt;

class SensitiveSetting
{
    public const PREFIX = 'ac:encrypted:v1:';

    /** @var list<string> */
    private const KEYS = [
        'chip_api_key',
        'chip_webhook_secret',
        'currency_data_feed_api_key',
        'facebook_login_app_secret',
        'fixer_access_key',
        'forge_api_key',
        'google_login_client_secret',
        'google_recaptcha_secret_key',
        'google_service_account_json',
        'mail_password',
        'mailchimp_api_key',
        'onesender_api_key',
        'twilio_token',
        'vonage_secret',
    ];

    public static function keys(): array
    {
        return self::KEYS;
    }

    public static function isSensitive(?string $key): bool
    {
        return is_string($key) && in_array($key, self::KEYS, true);
    }

    public static function encryptSerialized(string $serialized): string
    {
        return self::PREFIX.Crypt::encryptString($serialized);
    }

    public static function decryptSerialized(string $payload): string
    {
        if (! self::isEncrypted($payload)) {
            return $payload;
        }

        return Crypt::decryptString(substr($payload, strlen(self::PREFIX)));
    }

    public static function isEncrypted(?string $payload): bool
    {
        return is_string($payload) && str_starts_with($payload, self::PREFIX);
    }

    /**
     * Remove secrets from data passed into form controls while retaining a
     * separate configured flag for status indicators.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public static function redactForForm(array $settings): array
    {
        foreach (self::KEYS as $key) {
            $settings["{$key}_configured"] = filled($settings[$key] ?? null);

            if (array_key_exists($key, $settings)) {
                $settings[$key] = null;
            }
        }

        return $settings;
    }
}
