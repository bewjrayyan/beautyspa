<?php

namespace Modules\WhatsappBirthdayReminder\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\User\Services\OneSenderMessageLogger;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Support\PhoneNumber;

class BirthdayWhatsAppSender
{
    public function __construct(
        private BirthdayReminderConfig $config,
        private OneSenderWhatsAppService $whatsApp,
    ) {}


    /**
     * @return array{sent: bool, skipped: bool, image_url: ?string, error: ?string}
     */
    public function send(string $phone, string $message): array
    {
        $to = PhoneNumber::normalize($phone);

        if ($to === '') {
            return [
                'sent' => false,
                'skipped' => true,
                'image_url' => null,
                'error' => trans('whatsappbirthday::messages.invalid_phone'),
            ];
        }

        if (! $this->config->isReady()) {
            return [
                'sent' => false,
                'skipped' => true,
                'image_url' => null,
                'error' => $this->resolveSkipMessage(),
            ];
        }

        $imageUrl = $this->config->imageUrl();
        $context = [
            'source' => 'WhatsappBirthdayReminder',
            'dedupe_key' => 'birthday:'.now()->year.':'.$to,
            'immediate' => true,
        ];

        try {
            if ($imageUrl) {
                $sent = $this->whatsApp->sendImage($to, $imageUrl, $message, $context);
            } else {
                $sent = $this->whatsApp->sendNotification($to, $message, $context);
            }
        } catch (Exception $e) {
            Log::error('WhatsApp birthday reminder failed', [
                'phone' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'sent' => false,
                'skipped' => false,
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
            ];
        }

        if (! $sent) {
            return [
                'sent' => false,
                'skipped' => true,
                'image_url' => $imageUrl,
                'error' => $this->resolveSkipMessage(),
            ];
        }

        return [
            'sent' => true,
            'skipped' => false,
            'image_url' => $imageUrl,
            'error' => null,
        ];
    }


    private function resolveSkipMessage(): string
    {
        if (! OneSenderWhatsAppService::allowsRealOutbound()) {
            return trans('whatsappbirthday::messages.whatsapp_skipped_local');
        }

        if (! OneSenderWhatsAppService::isConfigured()) {
            return trans('whatsappbirthday::messages.whatsapp_not_configured');
        }

        if (app(OneSenderMessageLogger::class)->isSendingPaused()) {
            return trans('whatsappbirthday::messages.whatsapp_paused');
        }

        if (! $this->config->enabled()) {
            return trans('whatsappbirthday::messages.disabled');
        }

        return trans('whatsappbirthday::messages.whatsapp_not_delivered');
    }
}
