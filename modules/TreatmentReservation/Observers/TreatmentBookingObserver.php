<?php

namespace Modules\TreatmentReservation\Observers;

use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\BeauticianBookingNotificationService;

class TreatmentBookingObserver
{
    public function created(TreatmentBooking $booking): void
    {
        app(BeauticianBookingNotificationService::class)->notifyNewBooking($booking);
    }
}
