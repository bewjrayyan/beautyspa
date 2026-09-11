<?php

namespace Modules\TreatmentReservation\Services;

use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Support\PhoneNumber;

class BookingBeauticianWhatsAppService
{
    public function url(TreatmentBooking $booking): ?string
    {
        $beautician = $booking->beautician;
        $phone = PhoneNumber::normalize((string) ($beautician?->phone ?: $beautician?->user?->phone));

        if ($phone === '') {
            return null;
        }

        return 'https://wa.me/' . rawurlencode($phone) . '?text=' . rawurlencode($this->message($booking));
    }


    public function message(TreatmentBooking $booking): string
    {
        $isTba = $booking->isTbaSchedule();

        return trans('treatmentreservation::public.reschedule_whatsapp_message', [
            'beautician' => $booking->beautician?->name ?: trans('treatmentreservation::public.beautician'),
            'reference' => $booking->referenceCode(),
            'date' => $isTba
                ? trans('treatmentreservation::public.to_be_scheduled')
                : ($booking->appointment_date?->translatedFormat('d M Y') ?: '—'),
            'time' => $isTba
                ? trans('treatmentreservation::public.to_be_scheduled')
                : ($booking->displayAppointmentTime() ?: '—'),
        ]);
    }
}
