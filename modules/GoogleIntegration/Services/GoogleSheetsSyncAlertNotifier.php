<?php

namespace Modules\GoogleIntegration\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Modules\GoogleIntegration\Mail\GoogleSheetsSyncFailed;
use Modules\Order\Entities\Order;
use Modules\Sms\Exceptions\SmsException;
use Modules\Sms\Sms;

class GoogleSheetsSyncAlertNotifier
{
    public function notify(Order $order, string $message, string $trigger): void
    {
        if (! (bool) setting('google_sheets_sync_alert_enabled', false)) {
            return;
        }

        if (! GoogleSheetsService::isEnabled()) {
            return;
        }

        $cacheKey = 'google_sheets_sync_alert:' . $order->id;

        if (! Cache::add($cacheKey, true, now()->addHours(6))) {
            return;
        }

        $this->sendEmail($order, $message, $trigger);
        $this->sendWhatsApp($order, $message);
    }


    private function sendEmail(Order $order, string $message, string $trigger): void
    {
        $email = trim((string) setting('store_email', ''));

        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new GoogleSheetsSyncFailed($order, $message, $trigger));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }


    private function sendWhatsApp(Order $order, string $message): void
    {
        if (! (bool) setting('google_sheets_sync_alert_whatsapp_enabled', false)) {
            return;
        }

        $text = trans('setting::messages.google_sheets_alert_whatsapp_message', [
            'order' => $order->id,
            'error' => $message,
        ]);

        try {
            Sms::sendToAdmins($text);
        } catch (SmsException $exception) {
            report($exception);
        }
    }
}
