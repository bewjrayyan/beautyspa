<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Setting\Support\WhatsAppMessageTemplate;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
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

                    if ($this->deliverReminder($booking)) {
                        $sent++;
                    }
                }
            });

        return $sent;
    }


    public function sendManualReminder(TreatmentBooking $booking, bool $resend = false): bool
    {
        if (! setting('whatsapp_customer_reminder_enabled', true)) {
            throw new \InvalidArgumentException(TrLang::trans('admin.crm.reminder_disabled'));
        }

        if (! OneSenderWhatsAppService::isConfigured()) {
            throw new \InvalidArgumentException(TrLang::trans('admin.calendar.whatsapp_not_configured'));
        }

        if (! $this->canSendReminder($booking)) {
            throw new \InvalidArgumentException(TrLang::trans('admin.crm.reminder_not_eligible'));
        }

        if ($resend && $booking->customer_reminder_sent_at) {
            TreatmentBooking::query()
                ->whereKey($booking->id)
                ->update(['customer_reminder_sent_at' => null]);

            $booking->refresh();
        }

        return $this->deliverReminder($booking, logActivity: true);
    }


    public function canSendReminder(TreatmentBooking $booking): bool
    {
        $phone = trim((string) $booking->customer_phone);

        if ($phone === ''
            || ! $booking->appointment_date
            || ! $booking->appointment_time
            || ! in_array($booking->status, [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ], true)) {
            return false;
        }

        return OneSenderWhatsAppService::isConfigured()
            && (bool) setting('whatsapp_customer_reminder_enabled', true);
    }


    /**
     * @return array<string, mixed>
     */
    public function reminderMeta(TreatmentBooking $booking): array
    {
        $sentAt = $booking->customer_reminder_sent_at;

        return [
            'customer_reminder_sent_at' => $sentAt?->toIso8601String(),
            'customer_reminder_sent_label' => $sentAt
                ? $sentAt->format('d M Y, H:i')
                : null,
            'reminder_sent' => $sentAt !== null,
            'reminder_due' => $this->isDueForAutomaticReminder($booking),
            'can_send_reminder' => $this->canSendReminder($booking),
            'can_resend_reminder' => $this->canSendReminder($booking) && $sentAt !== null,
        ];
    }


    private function isDueForAutomaticReminder(TreatmentBooking $booking): bool
    {
        if ($booking->customer_reminder_sent_at || ! $this->canSendReminder($booking)) {
            return false;
        }

        $leadMinutes = max(15, (int) setting('whatsapp_customer_reminder_minutes', 120));
        $windowEnd = now()->addMinutes($leadMinutes);

        return $this->startsWithinWindow($booking, $windowEnd);
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
                    'dedupe_key' => 'booking:' . $booking->id . ':reminder:' . now()->format('YmdHi'),
                    'immediate' => $logActivity,
                ]
            );

            if (! $delivered) {
                $this->releaseReminderClaim($booking);

                return false;
            }

            if ($logActivity) {
                app(TreatmentBookingActivityLogger::class)->logReminderSent($booking);
            }

            return true;
        } catch (\Throwable $exception) {
            $this->releaseReminderClaim($booking);
            Log::error('Customer appointment reminder failed', [
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
        $trackingUrl = $this->trackingUrl($booking);

        $extraLines = implode("\n", array_filter([
            $beautician ? "Beautician: {$beautician}" : null,
            $trackingUrl ? "Jejak pesanan: {$trackingUrl}" : null,
        ]));
        $beauticianLine = $beautician ? "Beautician: {$beautician}" : '';
        $trackingLine = $trackingUrl ? "Jejak pesanan: {$trackingUrl}" : '';

        return WhatsAppMessageTemplate::render('whatsapp_customer_reminder_message', [
            'store' => $store,
            'customer' => $customer,
            'treatment' => $treatment,
            'date' => $date,
            'time' => $time,
            'beautician' => $beautician ?: '—',
            'tracking_url' => $trackingUrl ?: '',
            'extra_lines' => $extraLines,
            'beautician_line' => $beauticianLine,
            'tracking_line' => $trackingLine,
        ], implode("\n", array_filter([
            "⏰ *Peringatan Temujanji — {$store}*",
            '',
            "Hai {$customer},",
            '',
            "Rawatan: {$treatment}",
            "Tarikh: {$date}",
            "Masa: {$time}",
            $beautician ? "Beautician: {$beautician}" : null,
            $trackingUrl ? "Jejak pesanan: {$trackingUrl}" : null,
            '',
            'Sila hadir tepat pada masa. Terima kasih!',
        ])));
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
