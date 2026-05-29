<?php

namespace Modules\Sms;

use Exception;
use Modules\Sms\Exceptions\SmsException;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Support\PhoneNumber;

class Sms
{
    /**
     * Send a WhatsApp notification via OneSender (replaces legacy SMS gateways).
     *
     * @throws SmsException
     */
    public static function send($to, $message): void
    {
        $to = PhoneNumber::normalize((string) $to);

        if ($to === '') {
            return;
        }

        try {
            app(OneSenderWhatsAppService::class)->sendNotification($to, $message, [
                'source' => 'sms.gateway',
            ]);
        } catch (Exception $e) {
            throw new SmsException($e->getMessage());
        }
    }


    public static function sendToAdmins(string $message): void
    {
        try {
            app(OneSenderWhatsAppService::class)->notifyAdmins($message, [
                'source' => 'sms.admin',
            ]);
        } catch (Exception $e) {
            throw new SmsException($e->getMessage());
        }
    }
}
