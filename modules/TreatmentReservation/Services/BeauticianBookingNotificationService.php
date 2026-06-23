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


    private function buildMessage(TreatmentBooking $booking): string
    {
        $store = setting('store_name');
        $customer = $booking->customer_full_name ?: '—';
        $treatment = $booking->product?->name ?: '—';
        $date = $booking->appointment_date?->format('d M Y') ?: '—';
        $time = $booking->appointment_time ?: '—';
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
}
