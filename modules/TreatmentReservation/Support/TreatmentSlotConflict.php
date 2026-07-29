<?php

namespace Modules\TreatmentReservation\Support;

use Throwable;

final class TreatmentSlotConflict
{
    public static function causedBy(Throwable $exception): bool
    {
        do {
            $message = $exception->getMessage();

            if (
                str_contains($message, 'treatment_slot_unique')
                || str_contains($message, 'treatment_slot_order_unique')
                || str_contains($message, 'treatment_slot_booking_unique')
            ) {
                return true;
            }

            $exception = $exception->getPrevious();
        } while ($exception instanceof Throwable);

        return false;
    }
}
