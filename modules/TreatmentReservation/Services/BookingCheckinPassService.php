<?php

declare(strict_types=1);

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\URL;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

final class BookingCheckinPassService
{
    public function url(TreatmentBooking $booking): string
    {
        $expiresAt = $booking->appointment_date
            ? $booking->appointment_date->copy()->endOfDay()->addDay()
            : now()->addDay();

        if ($expiresAt->isPast()) {
            $expiresAt = now()->addMinutes(15);
        }

        return URL::temporarySignedRoute(
            'treatment_reservations.checkin.pass',
            $expiresAt,
            ['booking' => $booking->getKey()]
        );
    }
}
