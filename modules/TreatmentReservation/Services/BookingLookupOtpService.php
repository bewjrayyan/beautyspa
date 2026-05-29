<?php

namespace Modules\TreatmentReservation\Services;

use Exception;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Services\WhatsAppOtpService;
use Modules\User\Support\PhoneNumber;

class BookingLookupOtpService
{
    private const VERIFIED_SESSION_PREFIX = 'booking_lookup_verified.';


    /**
     * @throws Exception
     */
    public function send(string $phone): void
    {
        app(WhatsAppOtpService::class)->send($phone, 'booking');
    }


    /**
     * @throws Exception
     */
    public function verify(string $phone, string $otp): string
    {
        $normalized = app(WhatsAppOtpService::class)->verify($phone, $otp);

        session()->put(self::VERIFIED_SESSION_PREFIX . $normalized, [
            'verified' => true,
            'expires_at' => now()->addHours(2)->getTimestamp(),
        ]);

        return $normalized;
    }


    public function isVerified(string $normalized): bool
    {
        $data = session()->get(self::VERIFIED_SESSION_PREFIX . $normalized);

        if (! is_array($data) || empty($data['verified'])) {
            return false;
        }

        if (time() > (int) ($data['expires_at'] ?? 0)) {
            session()->forget(self::VERIFIED_SESSION_PREFIX . $normalized);

            return false;
        }

        return true;
    }


    public function forgetVerified(string $normalized): void
    {
        session()->forget(self::VERIFIED_SESSION_PREFIX . $normalized);
    }


    public function verifiedPhone(): ?string
    {
        $sessionPhone = session('booking_lookup_phone');

        if (! is_string($sessionPhone) || $sessionPhone === '') {
            return null;
        }

        return $this->isVerified($sessionPhone) ? $sessionPhone : null;
    }
}
