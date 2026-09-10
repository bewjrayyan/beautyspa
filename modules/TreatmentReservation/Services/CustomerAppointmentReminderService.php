<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Setting\Support\WhatsAppMessageTemplate;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Mail\AppointmentCheckinReminder;
use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
use Modules\User\Services\OneSenderWhatsAppService;

class CustomerAppointmentReminderService
{
    public function sendDueReminders(): int
    {
        $leadMinutes = max(15, min(1440, (int) setting('whatsapp_customer_reminder_minutes', 1440)));
        $windowEnd = now()->addMinutes($leadMinutes);
        $whatsappEnabled = (bool) setting('whatsapp_customer_reminder_enabled', true)
            && OneSenderWhatsAppService::isConfigured();
        $sent = 0;

        TreatmentBooking::query()
            ->with(['beautician.spaBranches', 'product', 'order'])
            ->whereNotNull('appointment_date')
            ->whereNotNull('appointment_time')
            ->where('status', TreatmentBooking::STATUS_PENDING)
            ->where(function ($query) use ($whatsappEnabled) {
                $query->where(function ($emailQuery) {
                    $emailQuery->whereNotNull('customer_email')
                        ->where('customer_email', '!=', '')
                        ->whereNull('customer_email_reminder_sent_at');
                });

                if ($whatsappEnabled) {
                    $query->orWhere(function ($whatsappQuery) {
                        $whatsappQuery->whereNotNull('customer_phone')
                            ->where('customer_phone', '!=', '')
                            ->whereNull('customer_reminder_sent_at');
                    });
                }
            })
            ->whereDate('appointment_date', '>=', today())
            ->whereDate('appointment_date', '<=', $windowEnd->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->chunkById(50, function ($bookings) use ($windowEnd, $whatsappEnabled, &$sent) {
                foreach ($bookings as $booking) {
                    if (! $this->startsWithinWindow($booking, $windowEnd)) {
                        continue;
                    }

                    $delivered = false;

                    if ($whatsappEnabled && ! $booking->customer_reminder_sent_at) {
                        try {
                            $delivered = $this->deliverReminder($booking) || $delivered;
                        } catch (\Throwable) {
                            // Email must still be attempted if WhatsApp delivery fails.
                        }
                    }

                    if (! $booking->customer_email_reminder_sent_at) {
                        $delivered = $this->deliverEmailReminder($booking) || $delivered;
                    }

                    if ($delivered) {
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


    public function canSendEmailReminder(TreatmentBooking $booking): bool
    {
        return filter_var(trim((string) $booking->customer_email), FILTER_VALIDATE_EMAIL) !== false
            && (bool) $booking->appointment_date
            && filled($booking->appointment_time)
            && $booking->status === TreatmentBooking::STATUS_PENDING;
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

        $leadMinutes = max(15, min(1440, (int) setting('whatsapp_customer_reminder_minutes', 1440)));
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


    private function deliverEmailReminder(TreatmentBooking $booking): bool
    {
        $email = trim((string) $booking->customer_email);

        if (! $this->canSendEmailReminder($booking) || ! $this->claimEmailReminder($booking)) {
            return false;
        }

        try {
            $locale = trim((string) ($booking->order?->locale ?: app()->getLocale()));
            $mailable = (new AppointmentCheckinReminder(
                $booking,
                app(BookingCheckinPassService::class)->url($booking),
            ))->locale($locale !== '' ? $locale : app()->getLocale());

            Mail::to($email)->send($mailable);
            app(TreatmentBookingActivityLogger::class)->logEmailReminderSent($booking);

            return true;
        } catch (\Throwable $exception) {
            $this->releaseEmailReminderClaim($booking);
            Log::error('Customer appointment email reminder failed', [
                'booking_id' => $booking->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }


    private function claimEmailReminder(TreatmentBooking $booking): bool
    {
        return TreatmentBooking::query()
            ->whereKey($booking->id)
            ->whereNull('customer_email_reminder_sent_at')
            ->where('status', TreatmentBooking::STATUS_PENDING)
            ->update(['customer_email_reminder_sent_at' => now()]) === 1;
    }


    private function releaseEmailReminderClaim(TreatmentBooking $booking): void
    {
        TreatmentBooking::query()
            ->whereKey($booking->id)
            ->update(['customer_email_reminder_sent_at' => null]);
    }


    private function buildMessage(TreatmentBooking $booking): string
    {
        $store = setting('store_name');
        $customer = $booking->customer_full_name ?: 'Pelanggan';
        $treatment = $booking->product?->name ?: '—';
        $date = $booking->appointment_date?->format('d M Y') ?: '—';
        $time = $booking->displayAppointmentTime() ?: '—';
        $beautician = $booking->beautician?->name;
        $trackingUrl = $this->trackingUrl($booking);
        $checkinUrl = $booking->status === TreatmentBooking::STATUS_PENDING
            ? app(BookingCheckinPassService::class)->url($booking)
            : null;

        $reference = $booking->referenceCode();
        $extraLines = implode("\n", array_filter([
            "Rujukan: {$reference}",
            $beautician ? "Beautician: {$beautician}" : null,
            $trackingUrl ? "Jejak pesanan: {$trackingUrl}" : null,
            $checkinUrl ? "Pas ketibaan: {$checkinUrl}" : null,
        ]));
        $beauticianLine = $beautician ? "Beautician: {$beautician}" : '';
        $trackingLine = $trackingUrl ? "Jejak pesanan: {$trackingUrl}" : '';
        $checkinLine = $checkinUrl ? "Pas ketibaan: {$checkinUrl}" : '';

        $message = WhatsAppMessageTemplate::render('whatsapp_customer_reminder_message', [
            'store' => $store,
            'customer' => $customer,
            'treatment' => $treatment,
            'date' => $date,
            'time' => $time,
            'reference' => $reference,
            'beautician' => $beautician ?: '—',
            'tracking_url' => $trackingUrl ?: '',
            'checkin_url' => $checkinUrl ?: '',
            'extra_lines' => $extraLines,
            'beautician_line' => $beauticianLine,
            'tracking_line' => $trackingLine,
            'checkin_line' => $checkinLine,
        ], implode("\n", array_filter([
            "⏰ *Peringatan Temujanji — {$store}*",
            '',
            "Hai {$customer},",
            '',
            "Rawatan: {$treatment}",
            "Tarikh: {$date}",
            "Masa: {$time}",
            "Rujukan: {$reference}",
            $beautician ? "Beautician: {$beautician}" : null,
            $trackingUrl ? "Jejak pesanan: {$trackingUrl}" : null,
            $checkinUrl ? "Pas ketibaan: {$checkinUrl}" : null,
            '',
            'Sila hadir tepat pada masa. Terima kasih!',
        ])));

        $message = $this->ensureReferenceLine($message, $reference);

        return $this->ensureCheckinLine($message, $checkinUrl);
    }


    private function ensureReferenceLine(string $message, string $reference): string
    {
        if (stripos($message, $reference) !== false) {
            return $message;
        }

        return rtrim($message) . "\nRujukan: {$reference}";
    }


    private function ensureCheckinLine(string $message, ?string $checkinUrl): string
    {
        if (! $checkinUrl || str_contains($message, $checkinUrl)) {
            return $message;
        }

        return rtrim($message)."\nPas ketibaan: {$checkinUrl}";
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
