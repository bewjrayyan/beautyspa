<?php

namespace Modules\TreatmentReservation\Services;

use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Entities\TreatmentBookingActivity;

class TreatmentBookingActivityLogger
{
    public function logStatusChange(TreatmentBooking $booking, ?string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        TreatmentBookingActivity::create([
            'treatment_booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'action' => TreatmentBookingActivity::ACTION_STATUS_CHANGED,
            'from_value' => $from,
            'to_value' => $to,
        ]);
    }


    public function logBeauticianNotes(TreatmentBooking $booking, ?string $from, ?string $to): void
    {
        if ((string) $from === (string) $to) {
            return;
        }

        TreatmentBookingActivity::create([
            'treatment_booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'action' => TreatmentBookingActivity::ACTION_BEAUTICIAN_NOTES_UPDATED,
            'from_value' => $from,
            'to_value' => $to,
        ]);
    }


    public function logWhatsAppSent(TreatmentBooking $booking): void
    {
        TreatmentBookingActivity::create([
            'treatment_booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'action' => TreatmentBookingActivity::ACTION_WHATSAPP_SENT,
            'to_value' => $booking->customer_phone,
        ]);
    }


    public function logReminderSent(TreatmentBooking $booking): void
    {
        TreatmentBookingActivity::create([
            'treatment_booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'action' => TreatmentBookingActivity::ACTION_REMINDER_SENT,
            'to_value' => $booking->customer_phone,
        ]);
    }


    public function logBeauticianReminderSent(TreatmentBooking $booking): void
    {
        $booking->loadMissing('beautician');

        TreatmentBookingActivity::create([
            'treatment_booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'action' => TreatmentBookingActivity::ACTION_BEAUTICIAN_REMINDER_SENT,
            'to_value' => $booking->beautician?->phone,
        ]);
    }


    public function logCreated(TreatmentBooking $booking, ?int $userId = null): void
    {
        TreatmentBookingActivity::create([
            'treatment_booking_id' => $booking->id,
            'user_id' => $userId ?? auth()->id(),
            'action' => TreatmentBookingActivity::ACTION_CREATED,
            'to_value' => $booking->source,
        ]);
    }


    public function logUpdated(TreatmentBooking $booking, ?int $userId = null): void
    {
        TreatmentBookingActivity::create([
            'treatment_booking_id' => $booking->id,
            'user_id' => $userId ?? auth()->id(),
            'action' => TreatmentBookingActivity::ACTION_UPDATED,
            'to_value' => $booking->appointment_date?->format('Y-m-d') . ' ' . $booking->appointment_time,
        ]);
    }
}
