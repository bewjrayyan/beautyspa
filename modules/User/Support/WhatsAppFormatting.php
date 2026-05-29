<?php

namespace Modules\User\Support;

class WhatsAppFormatting
{
    /**
     * WhatsApp bold: wrap a value in asterisks (*549839*).
     */
    public static function boldOtp(string $code): string
    {
        $code = trim($code);

        if ($code === '') {
            return '';
        }

        if (str_starts_with($code, '*') && str_ends_with($code, '*')) {
            return $code;
        }

        return '*' . $code . '*';
    }


    /**
     * Bold every standalone 6-digit code in a message (OTP length for this app).
     */
    public static function boldOtpCodesInMessage(string $message): string
    {
        return preg_replace(
            '/(?<!\*)\b(\d{6})\b(?!\*)/',
            '*$1*',
            $message
        ) ?? $message;
    }
}
