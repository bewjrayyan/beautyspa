<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\Log;
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
                'immediate' => true,
            ]
        );

        if (! $delivered) {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.calendar.whatsapp_not_configured'));
        }
    }


    public function notifyRescheduled(TreatmentBooking $booking, ?string $oldDate, ?string $oldTime): bool
    {
        if (! $this->canSend($booking)) {
            return false;
        }

        $phone = PhoneNumber::normalize((string) $booking->customer_phone);

        try {
            return app(OneSenderWhatsAppService::class)->sendNotification(
                $phone,
                $this->buildRescheduleMessage($booking, $oldDate, $oldTime),
                [
                    'source' => 'treatment.booking.rescheduled.customer',
                    'dedupe_key' => sprintf(
                        'booking:%s:rescheduled:customer:%s:%s',
                        $booking->id,
                        $booking->appointment_date?->format('Ymd') ?: 'date',
                        str_replace(':', '', (string) $booking->appointment_time),
                    ),
                ],
            );
        } catch (\Throwable $exception) {
            Log::error('Customer reschedule WhatsApp failed', [
                'booking_id' => $booking->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }


    private function buildMessage(TreatmentBooking $booking): string
    {
        $booking->loadMissing(['beautician', 'product']);

        $store = setting('store_name');
        $customer = $booking->customer_full_name ?: 'Pelanggan';
        $treatment = $booking->product?->name ?: '—';
        $date = $booking->appointment_date?->format('d M Y') ?: '—';
        $time = $booking->displayAppointmentTime() ?: '—';
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


    private function buildRescheduleMessage(TreatmentBooking $booking, ?string $oldDate, ?string $oldTime): string
    {
        $booking->loadMissing(['beautician', 'product', 'order']);

        return trans('treatmentreservation::admin.reschedule.customer_message', [
            'store' => setting('store_name'),
            'customer' => $booking->customer_full_name ?: trans(
                'treatmentreservation::admin.reschedule.customer_fallback',
                [],
                $booking->order?->locale ?: app()->getLocale(),
            ),
            'treatment' => $booking->product?->name ?: '—',
            'old_date' => $oldDate ? \Illuminate\Support\Carbon::parse($oldDate)->format('d M Y') : '—',
            'old_time' => $oldTime ?: '—',
            'date' => $booking->appointment_date?->format('d M Y') ?: '—',
            'time' => $booking->displayAppointmentTime() ?: '—',
            'beautician' => $booking->beautician?->name ?: '—',
        ], $booking->order?->locale ?: app()->getLocale());
    }
}
