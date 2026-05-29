<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Services\OneSenderWhatsAppService;

class BeauticianAppointmentReminderService
{
    public function sendDueReminders(): int
    {
        if (! setting('whatsapp_beautician_reminder_enabled', true)) {
            return 0;
        }

        $leadMinutes = max(15, (int) setting('whatsapp_beautician_reminder_minutes', 120));
        $windowEnd = now()->addMinutes($leadMinutes);
        $sent = 0;

        TreatmentBooking::query()
            ->with(['beautician', 'product'])
            ->whereNotNull('beautician_id')
            ->whereNotNull('appointment_date')
            ->whereNotNull('appointment_time')
            ->whereNull('reminder_sent_at')
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
                        $booking->update(['reminder_sent_at' => now()]);
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
        $phone = trim((string) $booking->beautician?->phone);

        if ($phone === '') {
            return false;
        }

        try {
            return app(OneSenderWhatsAppService::class)->sendNotification(
                $phone,
                $this->buildMessage($booking),
                [
                    'source' => 'treatment.beautician.reminder',
                    'dedupe_key' => 'booking:' . $booking->id . ':beautician_reminder',
                ]
            );
        } catch (\Throwable $exception) {
            Log::error('Beautician appointment reminder failed', [
                'booking_id' => $booking->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }


    private function buildMessage(TreatmentBooking $booking): string
    {
        $store = setting('store_name');
        $customer = $booking->customer_full_name ?: '—';
        $treatment = $booking->product?->name ?: '—';
        $date = $booking->appointment_date?->format('d M Y') ?: '—';
        $time = $booking->appointment_time ?: '—';
        $portalUrl = route('admin.treatment_reservations.portal');

        return implode("\n", [
            "⏰ *Peringatan Temujanji — {$store}*",
            '',
            "Pelanggan: {$customer}",
            "Rawatan: {$treatment}",
            "Tarikh: {$date}",
            "Masa: {$time}",
            '',
            "Buka job sheet: {$portalUrl}",
        ]);
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
