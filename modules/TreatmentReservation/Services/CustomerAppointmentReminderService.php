<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Services\OneSenderWhatsAppService;

class CustomerAppointmentReminderService
{
    public function sendDueReminders(): int
    {
        if (! setting('whatsapp_customer_reminder_enabled', true)) {
            return 0;
        }

        $leadMinutes = max(15, (int) setting('whatsapp_customer_reminder_minutes', 120));
        $windowEnd = now()->addMinutes($leadMinutes);
        $sent = 0;

        TreatmentBooking::query()
            ->with(['beautician', 'product'])
            ->whereNotNull('appointment_date')
            ->whereNotNull('appointment_time')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->whereNull('customer_reminder_sent_at')
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->whereDate('appointment_date', '>=', today())
            ->whereDate('appointment_date', '<=', $windowEnd->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->chunkById(50, function ($bookings) use ($windowEnd, &$sent) {
                foreach ($bookings as $booking) {
                    if (! $this->startsWithinWindow($booking, $windowEnd)) {
                        continue;
                    }

                    if ($this->sendReminder($booking)) {
                        $sent++;
                    }
                }
            });

        return $sent;
    }


    private function startsWithinWindow(TreatmentBooking $booking, Carbon $windowEnd): bool
    {
        $startsAt = $this->appointmentDateTime($booking);

        if (! $startsAt) {
            return false;
        }

        return $startsAt->isBetween(now(), $windowEnd);
    }


    private function sendReminder(TreatmentBooking $booking): bool
    {
        $phone = trim((string) $booking->customer_phone);

        if ($phone === '' || ! $this->claimReminder($booking)) {
            return false;
        }

        try {
            $delivered = app(OneSenderWhatsAppService::class)->sendNotification(
                $phone,
                $this->buildMessage($booking),
                [
                    'source' => 'treatment.booking.reminder',
                    'dedupe_key' => 'booking:' . $booking->id . ':reminder',
                ]
            );

            if (! $delivered) {
                $this->releaseReminderClaim($booking);

                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            $this->releaseReminderClaim($booking);
            Log::error('Customer appointment reminder failed', [
                'booking_id' => $booking->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }


    private function claimReminder(TreatmentBooking $booking): bool
    {
        return TreatmentBooking::query()
            ->whereKey($booking->id)
            ->whereNull('customer_reminder_sent_at')
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->update(['customer_reminder_sent_at' => now()]) === 1;
    }


    private function releaseReminderClaim(TreatmentBooking $booking): void
    {
        TreatmentBooking::query()
            ->whereKey($booking->id)
            ->update(['customer_reminder_sent_at' => null]);
    }


    private function buildMessage(TreatmentBooking $booking): string
    {
        $store = setting('store_name');
        $customer = $booking->customer_full_name ?: 'Pelanggan';
        $treatment = $booking->product?->name ?: '—';
        $date = $booking->appointment_date?->format('d M Y') ?: '—';
        $time = $booking->appointment_time ?: '—';
        $beautician = $booking->beautician?->name;

        $lines = [
            "⏰ *Peringatan Temujanji — {$store}*",
            '',
            "Hai {$customer},",
            '',
            "Rawatan: {$treatment}",
            "Tarikh: {$date}",
            "Masa: {$time}",
        ];

        if ($beautician) {
            $lines[] = "Beautician: {$beautician}";
        }

        $trackingUrl = $this->trackingUrl($booking);

        if ($trackingUrl) {
            $lines[] = '';
            $lines[] = "Jejak pesanan: {$trackingUrl}";
        }

        $lines[] = '';
        $lines[] = 'Sila hadir tepat pada masa. Terima kasih!';

        return implode("\n", $lines);
    }


    private function trackingUrl(TreatmentBooking $booking): ?string
    {
        if (! $booking->order_id) {
            return null;
        }

        $base = rtrim((string) setting('whatsapp_order_tracking_url', ''), '/');

        if ($base === '') {
            return null;
        }

        return "{$base}/{$booking->order_id}";
    }


    private function appointmentDateTime(TreatmentBooking $booking): ?Carbon
    {
        if (! $booking->appointment_date || ! $booking->appointment_time) {
            return null;
        }

        try {
            return Carbon::parse(
                $booking->appointment_date->format('Y-m-d') . ' ' . $booking->appointment_time
            );
        } catch (\Throwable) {
            return null;
        }
    }
}
