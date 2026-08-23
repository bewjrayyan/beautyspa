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

        $booking->loadMissing(['beautician', 'product']);

        $phone = trim((string) $booking->beautician?->phone);

        if ($phone === '') {
            return;
        }

        try {
            app(OneSenderWhatsAppService::class)->sendNotification(
                $phone,
                $this->buildMessage($booking),
                [
                    'source' => 'treatment.beautician.new_booking',
                    'dedupe_key' => 'booking:' . $booking->id . ':beautician_new',
                ]
            );
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

        $booking->loadMissing(['beautician', 'product']);
        $phone = trim((string) $booking->beautician?->phone);

        if ($phone === '') {
            return false;
        }

        try {
            return app(OneSenderWhatsAppService::class)->sendNotification(
                $phone,
                $this->buildRescheduleMessage($booking, $oldDate, $oldTime),
                [
                    'source' => 'treatment.beautician.rescheduled',
                    'dedupe_key' => sprintf(
                        'booking:%s:rescheduled:beautician:%s:%s',
                        $booking->id,
                        $booking->appointment_date?->format('Ymd') ?: 'date',
                        str_replace(':', '', (string) $booking->appointment_time),
                    ),
                ],
            );
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

        return WhatsAppMessageTemplate::render('whatsapp_beautician_new_booking_message', [
            'store' => $store,
            'customer' => $customer,
            'treatment' => $treatment,
            'date' => $date,
            'time' => $time,
            'portal_url' => $portalUrl,
        ], implode("\n", [
            "📅 *Tempahan Baharu — {$store}*",
            '',
            "Pelanggan: {$customer}",
            "Rawatan: {$treatment}",
            "Tarikh: {$date}",
            "Masa: {$time}",
            '',
            "Buka job sheet: {$portalUrl}",
        ]));
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
