<?php

declare(strict_types=1);

namespace Modules\TreatmentReservation\Services;

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

        $relative = aestheticcart_subdirectory_safe_temporary_signed_route(
            'treatment_reservations.checkin.pass',
            $expiresAt,
            ['booking' => $booking->getKey()]
        );

        return aestheticcart_absolute_from_relative_path($relative);
    }
}
