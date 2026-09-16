<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\Log;
use Modules\Setting\Support\WhatsAppMessageTemplate;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Services\OneSenderWhatsAppService;

class BeauticianBookingNotificationService
{
    public function notifyNewBooking(TreatmentBooking $booking): void
    {
        if (! setting('whatsapp_beautician_new_booking_enabled', true)) {
            return;
        }

        if ($booking->status === TreatmentBooking::STATUS_CANCELED) {
            return;
        }

        $booking->loadMissing(['beautician.user', 'product']);

        $recipients = app(BeauticianWhatsAppRecipientResolver::class)->resolve($booking);

        if ($recipients === []) {
            return;
        }

        try {
            foreach ($recipients as $phone) {
                app(OneSenderWhatsAppService::class)->sendNotification(
                    $phone,
                    $this->buildMessage($booking),
                    [
                        'source' => 'treatment.beautician.new_booking',
                        'dedupe_key' => 'booking:' . $booking->id . ':beautician_new:recipient:'
                            . substr(hash('sha256', $phone), 0, 16),
                    ]
                );
            }
        } catch (\Throwable $exception) {
            Log::error('Beautician new booking WhatsApp failed', [
                'booking_id' => $booking->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }


    public function notifyRescheduledBooking(TreatmentBooking $booking, ?string $oldDate, ?string $oldTime): bool
    {
        if ($booking->status === TreatmentBooking::STATUS_CANCELED) {
            return false;
        }

        $booking->loadMissing(['beautician.user', 'product']);
        $recipients = app(BeauticianWhatsAppRecipientResolver::class)->resolve($booking);

        if ($recipients === []) {
            return false;
        }

        try {
            $delivered = false;

            foreach ($recipients as $phone) {
                $delivered = app(OneSenderWhatsAppService::class)->sendNotification(
                    $phone,
                    $this->buildRescheduleMessage($booking, $oldDate, $oldTime),
                    [
                        'source' => 'treatment.beautician.rescheduled',
                        'dedupe_key' => sprintf(
                            'booking:%s:rescheduled:beautician:%s:%s:recipient:%s',
                            $booking->id,
                            $booking->appointment_date?->format('Ymd') ?: 'date',
                            str_replace(':', '', (string) $booking->appointment_time),
                            substr(hash('sha256', $phone), 0, 16),
                        ),
                    ],
                ) || $delivered;
            }

            return $delivered;
        } catch (\Throwable $exception) {
            Log::error('Beautician reschedule WhatsApp failed', [
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
        $time = $booking->displayAppointmentTime() ?: '—';
        $portalUrl = route('admin.treatment_reservations.portal');
        $reference = $booking->referenceCode();

        $message = WhatsAppMessageTemplate::render('whatsapp_beautician_new_booking_message', [
            'store' => $store,
            'customer' => $customer,
            'treatment' => $treatment,
            'date' => $date,
            'time' => $time,
            'reference' => $reference,
            'portal_url' => $portalUrl,
        ], implode("\n", [
            "📅 *Tempahan Baharu — {$store}*",
            '',
            "Pelanggan: {$customer}",
            "Rawatan: {$treatment}",
            "Tarikh: {$date}",
            "Masa: {$time}",
            "Rujukan: {$reference}",
            '',
            "Buka job sheet: {$portalUrl}",
        ]));

        return $this->ensureReferenceLine($message, $reference);
    }


    private function ensureReferenceLine(string $message, string $reference): string
    {
        if (stripos($message, $reference) !== false) {
            return $message;
        }

        return rtrim($message) . "\nRujukan: {$reference}";
    }


    private function buildRescheduleMessage(TreatmentBooking $booking, ?string $oldDate, ?string $oldTime): string
    {
        $portalUrl = route('admin.treatment_reservations.portal.calendar_page', [
            'focus' => 1,
            'booking_id' => $booking->id,
            'month' => $booking->appointment_date?->format('Y-m'),
        ]);

        return trans('treatmentreservation::admin.reschedule.beautician_message', [
            'store' => setting('store_name'),
            'customer' => $booking->customer_full_name ?: '—',
            'treatment' => $booking->product?->name ?: '—',
            'old_date' => $oldDate ? \Illuminate\Support\Carbon::parse($oldDate)->format('d M Y') : '—',
            'old_time' => $oldTime ?: '—',
            'date' => $booking->appointment_date?->format('d M Y') ?: '—',
            'time' => $booking->displayAppointmentTime() ?: '—',
            'portal_url' => $portalUrl,
        ]);
    }
}
