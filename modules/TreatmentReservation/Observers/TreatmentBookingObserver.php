<?php

namespace Modules\TreatmentReservation\Observers;

use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\BeauticianBookingNotificationService;

class TreatmentBookingObserver
{
    public function updating(TreatmentBooking $booking): void
    {
        if (
            ! $booking->isDirty([
                'appointment_date',
                'appointment_time',
                'schedule_status',
                'beautician_id',
            ])
        ) {
            return;
        }

        if ($booking->isTbaSchedule()) {
            $booking->tba_reminder_sent_at = null;
        }
    }


    public function created(TreatmentBooking $booking): void
    {
        app(BeauticianBookingNotificationService::class)->notifyNewBooking($booking);
    }
}
