<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\Log;
use Modules\Setting\Support\SettingValues;
use Modules\Setting\Support\WhatsAppMessageTemplate;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Services\OneSenderWhatsAppService;

class CustomerCompletedNotificationService
{
    /**
     * Send at most once per booking. Disabled unless enabled in Settings → SMS → WhatsApp.
     */
    public function sendIfDue(TreatmentBooking $booking): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        if ($booking->status !== TreatmentBooking::STATUS_COMPLETED) {
            return false;
        }

        $phone = trim((string) $booking->customer_phone);

        if ($phone === '') {
            return false;
        }

        if (! $this->claimSend($booking)) {
            return false;
        }

        try {
            $delivered = app(OneSenderWhatsAppService::class)->sendNotification(
                $phone,
                $this->buildMessage($booking),
                [
                    'source' => 'treatment.booking.completed',
                    'dedupe_key' => 'booking:' . $booking->id . ':completed',
                ]
            );

            if (! $delivered) {
                $this->releaseSendClaim($booking);

                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            // Keep claim set so a failed API call cannot trigger endless retries.
            Log::error('Customer completed notification failed', [
                'booking_id' => $booking->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }


    public function isEnabled(): bool
    {
        return SettingValues::isTruthy('whatsapp_customer_completed_enabled');
    }


    private function claimSend(TreatmentBooking $booking): bool
    {
        return TreatmentBooking::query()
            ->whereKey($booking->id)
            ->where('status', TreatmentBooking::STATUS_COMPLETED)
            ->whereNull('completed_notification_sent_at')
            ->update(['completed_notification_sent_at' => now()]) === 1;
    }


    private function releaseSendClaim(TreatmentBooking $booking): void
    {
        TreatmentBooking::query()
            ->whereKey($booking->id)
            ->update(['completed_notification_sent_at' => null]);
    }


    private function buildMessage(TreatmentBooking $booking): string
    {
        $booking->loadMissing('product');

        $store = setting('store_name');
        $customer = $booking->customer_full_name ?: 'Pelanggan';
        $treatment = $booking->product?->name ?: '—';

        $lines = [
            "✨ *Terima Kasih — {$store}*",
            '',
            "Hai {$customer},",
            '',
            "Rawatan *{$treatment}* anda telah selesai.",
            '',
            'Kami berharap anda berpuas hati. Jumpa lagi!',
        ];

        return WhatsAppMessageTemplate::render('whatsapp_customer_completed_message', [
            'store' => $store,
            'customer' => $customer,
            'treatment' => $treatment,
        ], implode("\n", $lines));
    }
}
