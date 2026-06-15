<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
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

                    if ($this->deliverReminder($booking)) {
                        $sent++;
                    }
                }
            });

        return $sent;
    }


    public function sendManualReminder(TreatmentBooking $booking, bool $resend = false): bool
    {
        if (! setting('whatsapp_beautician_reminder_enabled', true)) {
            throw new \InvalidArgumentException(TrLang::trans('admin.crm.beautician_reminder_disabled'));
        }

        if (! OneSenderWhatsAppService::isConfigured()) {
            throw new \InvalidArgumentException(TrLang::trans('admin.calendar.whatsapp_not_configured'));
        }

        if (! $this->canSendReminder($booking)) {
            throw new \InvalidArgumentException(TrLang::trans('admin.crm.beautician_reminder_not_eligible'));
        }

        if ($resend && $booking->reminder_sent_at) {
            TreatmentBooking::query()
                ->whereKey($booking->id)
                ->update(['reminder_sent_at' => null]);

            $booking->refresh();
        }

        return $this->deliverReminder($booking, logActivity: true);
    }


    public function canSendReminder(TreatmentBooking $booking): bool
    {
        $booking->loadMissing('beautician');

        $phone = trim((string) $booking->beautician?->phone);

        if ($phone === ''
            || ! $booking->beautician_id
            || ! $booking->appointment_date
            || ! $booking->appointment_time
            || ! in_array($booking->status, [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ], true)) {
            return false;
        }

        return OneSenderWhatsAppService::isConfigured()
            && (bool) setting('whatsapp_beautician_reminder_enabled', true);
    }


    /**
     * @return array<string, mixed>
     */
    public function reminderMeta(TreatmentBooking $booking): array
    {
        $sentAt = $booking->reminder_sent_at;

        return [
            'beautician_reminder_sent_at' => $sentAt?->toIso8601String(),
            'beautician_reminder_sent_label' => $sentAt
                ? $sentAt->format('d M Y, H:i')
                : null,
            'beautician_reminder_sent' => $sentAt !== null,
            'can_send_beautician_reminder' => $this->canSendReminder($booking),
            'can_resend_beautician_reminder' => $this->canSendReminder($booking) && $sentAt !== null,
        ];
    }


    private function startsWithinWindow(TreatmentBooking $booking, Carbon $windowEnd): bool
    {
        $startsAt = $this->appointmentDateTime($booking);

        if (! $startsAt) {
            return false;
        }

        return $startsAt->isBetween(now(), $windowEnd);
    }


    private function deliverReminder(TreatmentBooking $booking, bool $logActivity = false): bool
    {
        $booking->loadMissing('beautician');

        $phone = trim((string) $booking->beautician?->phone);

        if ($phone === '' || ! $this->claimReminder($booking)) {
            return false;
        }

        try {
            $delivered = app(OneSenderWhatsAppService::class)->sendNotification(
                $phone,
                $this->buildMessage($booking),
                [
                    'source' => 'treatment.beautician.reminder',
                    'dedupe_key' => 'booking:' . $booking->id . ':beautician_reminder:' . now()->format('YmdHi'),
                    'immediate' => $logActivity,
                ]
            );

            if (! $delivered) {
                $this->releaseReminderClaim($booking);

                return false;
            }

            if ($logActivity) {
                app(TreatmentBookingActivityLogger::class)->logBeauticianReminderSent($booking);
            }

            return true;
        } catch (\Throwable $exception) {
            $this->releaseReminderClaim($booking);
            Log::error('Beautician appointment reminder failed', [
                'booking_id' => $booking->id,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }


    private function claimReminder(TreatmentBooking $booking): bool
    {
        return TreatmentBooking::query()
            ->whereKey($booking->id)
            ->whereNull('reminder_sent_at')
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->update(['reminder_sent_at' => now()]) === 1;
    }


    private function releaseReminderClaim(TreatmentBooking $booking): void
    {
        TreatmentBooking::query()
            ->whereKey($booking->id)
            ->update(['reminder_sent_at' => null]);
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
