<?php

namespace Modules\TreatmentReservation\Services;

use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Support\PhoneNumber;

class BookingCustomerWhatsAppService
{
    public function canSend(TreatmentBooking $booking): bool
    {
        return filled(trim((string) $booking->customer_phone))
            && OneSenderWhatsAppService::isConfigured();
    }


    public function send(TreatmentBooking $booking, ?string $message = null): void
    {
        $phone = PhoneNumber::normalize((string) $booking->customer_phone);

        if ($phone === '') {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.calendar.whatsapp_not_configured'));
        }

        $delivered = app(OneSenderWhatsAppService::class)->sendNotification(
            $phone,
            $message !== null && trim($message) !== ''
                ? trim($message)
                : $this->buildMessage($booking),
            [
                'source' => 'treatment.booking.manual',
                'dedupe_key' => 'booking:' . $booking->id . ':manual',
            ]
        );

        if (! $delivered) {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.calendar.whatsapp_not_configured'));
        }
    }


    private function buildMessage(TreatmentBooking $booking): string
    {
        $booking->loadMissing(['beautician', 'product']);

        $store = setting('store_name');
        $customer = $booking->customer_full_name ?: 'Pelanggan';
        $treatment = $booking->product?->name ?: '—';
        $date = $booking->appointment_date?->format('d M Y') ?: '—';
        $time = $booking->appointment_time ?: '—';
        $beautician = $booking->beautician?->name;

        $lines = [
            "Hai {$customer},",
            '',
            "Ini *{$store}* mengenai tempahan rawatan anda:",
            '',
            "Rawatan: {$treatment}",
            "Tarikh: {$date}",
            "Masa: {$time}",
        ];

        if ($beautician) {
            $lines[] = "Beautician: {$beautician}";
        }

        $lines[] = '';
        $lines[] = 'Sila balas mesej ini jika anda ada sebarang pertanyaan. Terima kasih!';

        return implode("\n", $lines);
    }
}
