<?php

namespace Modules\User\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Modules\User\Support\PhoneNumber;
use Modules\User\Support\WhatsAppFormatting;

class WhatsAppOtpService
{
    private const OTP_SESSION_PREFIX = 'whatsapp_otp.';

    /** @deprecated Legacy session key; cleared on send for migration */
    private const RATE_LIMIT_SESSION_PREFIX = 'whatsapp_otp_send.';


    /**
     * @param  string  $purpose  Rate-limit scope: login, booking, admin (separate counters per phone).
     * @throws Exception
     */
    public function send(string $phone, string $purpose = 'login'): void
    {
        $normalized = PhoneNumber::normalize($phone);

        if ($normalized === '') {
            throw new Exception(trans('user::messages.whatsapp_otp.invalid_phone'));
        }

        $this->clearLegacySessionRateLimit($normalized);

        $rateLimitKey = $this->sendRateLimitKey($purpose, $normalized);
        $maxAttempts = max(3, (int) setting('whatsapp_otp_send_max_attempts', 5));
        $decaySeconds = max(60, (int) setting('whatsapp_otp_send_decay_minutes', 10) * 60);

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            throw new Exception(trans('user::messages.whatsapp_otp.too_many_attempts', [
                'minutes' => max(1, (int) ceil($seconds / 60)),
            ]));
        }

        $otp = (string) random_int(100000, 999999);
        $expiryMinutes = max(5, (int) setting('whatsapp_otp_expiry_minutes', 5));

        $payload = [
            'otp' => $otp,
            'attempts' => 0,
            'expires_at' => now()->addMinutes($expiryMinutes)->getTimestamp(),
        ];

        $this->storeOtp($normalized, $payload, $expiryMinutes);

        $storeName = setting('store_name');
        $message = trans('user::messages.whatsapp_otp.sms_message', [
            'otp' => WhatsAppFormatting::boldOtp($otp),
            'minutes' => $expiryMinutes,
            'store' => $storeName,
        ]);

        try {
            app(OneSenderWhatsAppService::class)->sendOtp($normalized, $message);
        } catch (Exception $e) {
            $this->forgetOtp($normalized);

            throw $e;
        }

        RateLimiter::hit($rateLimitKey, $decaySeconds);
    }


    /**
     * @throws Exception
     */
    public function verify(string $phone, string $otp): string
    {
        $normalized = PhoneNumber::normalize($phone);

        if ($normalized === '') {
            throw new Exception(trans('user::messages.whatsapp_otp.invalid_phone'));
        }

        $sessionKey = $this->otpSessionKey($normalized);
        $data = $this->retrieveOtp($normalized);

        if (! is_array($data) || empty($data['otp'])) {
            throw new Exception(trans('user::messages.whatsapp_otp.expired'));
        }

        if (time() > (int) ($data['expires_at'] ?? 0)) {
            $this->forgetOtp($normalized);

            throw new Exception(trans('user::messages.whatsapp_otp.expired'));
        }

        $data['attempts'] = ($data['attempts'] ?? 0) + 1;

        $maxVerifyAttempts = max(5, (int) setting('whatsapp_otp_verify_max_attempts', 8));

        if ($data['attempts'] > $maxVerifyAttempts) {
            $this->forgetOtp($normalized);

            throw new Exception(trans('user::messages.whatsapp_otp.too_many_verify_attempts'));
        }

        $this->storeOtp($normalized, $data, $this->remainingMinutes($data));

        if (! hash_equals((string) $data['otp'], trim($otp))) {
            throw new Exception(trans('user::messages.whatsapp_otp.invalid_code'));
        }

        $this->forgetOtp($normalized);

        return $normalized;
    }


    private function storeOtp(string $normalized, array $data, int $expiryMinutes): void
    {
        session()->put($this->otpSessionKey($normalized), $data);
        session()->save();

        Cache::store('file')->put(
            $this->otpSessionKey($normalized),
            $data,
            now()->addMinutes(max(1, $expiryMinutes))
        );
    }


    private function retrieveOtp(string $normalized): ?array
    {
        $key = $this->otpSessionKey($normalized);
        $data = session()->get($key);

        if (is_array($data) && ! empty($data['otp'])) {
            return $data;
        }

        $data = Cache::store('file')->get($key);

        if (is_array($data) && ! empty($data['otp'])) {
            session()->put($key, $data);

            return $data;
        }

        foreach (PhoneNumber::variants($normalized) as $variant) {
            if ($variant === $normalized) {
                continue;
            }

            $variantKey = $this->otpSessionKey($variant);
            $data = session()->get($variantKey) ?? Cache::store('file')->get($variantKey);

            if (is_array($data) && ! empty($data['otp'])) {
                session()->put($key, $data);
                Cache::store('file')->put($key, $data, $this->cacheTtl($data));

                return $data;
            }
        }

        return null;
    }


    private function forgetOtp(string $normalized): void
    {
        $key = $this->otpSessionKey($normalized);

        session()->forget($key);
        Cache::store('file')->forget($key);

        foreach (PhoneNumber::variants($normalized) as $variant) {
            $variantKey = $this->otpSessionKey($variant);
            session()->forget($variantKey);
            Cache::store('file')->forget($variantKey);
        }
    }


    private function remainingMinutes(array $data): int
    {
        $seconds = (int) ($data['expires_at'] ?? 0) - time();

        return max(1, (int) ceil($seconds / 60));
    }


    private function cacheTtl(array $data): \DateTimeInterface
    {
        $expiresAt = (int) ($data['expires_at'] ?? 0);

        if ($expiresAt > time()) {
            return now()->setTimestamp($expiresAt);
        }

        return now()->addMinutes(5);
    }


    private function otpSessionKey(string $normalized): string
    {
        return self::OTP_SESSION_PREFIX . $normalized;
    }


    private function sendRateLimitKey(string $purpose, string $normalized): string
    {
        $purpose = preg_replace('/[^a-z0-9_-]/', '', strtolower($purpose)) ?: 'login';

        return 'whatsapp_otp_send:' . $purpose . ':' . $normalized;
    }


    private function clearLegacySessionRateLimit(string $normalized): void
    {
        session()->forget(self::RATE_LIMIT_SESSION_PREFIX . $normalized);

        foreach (PhoneNumber::variants($normalized) as $variant) {
            session()->forget(self::RATE_LIMIT_SESSION_PREFIX . $variant);
        }
    }
}
