<?php

namespace Modules\TreatmentReservation\Support;

use Modules\TreatmentReservation\Entities\TreatmentBooking;

class CustomerVisitLabel
{
    public static function forBooking(TreatmentBooking $booking, int $completedVisitCount): string
    {
        return self::format(self::visitNumber($booking, $completedVisitCount));
    }


    public static function visitNumber(TreatmentBooking $booking, int $completedVisitCount): int
    {
        if ($booking->status === TreatmentBooking::STATUS_COMPLETED) {
            return max(1, $completedVisitCount);
        }

        return max(1, $completedVisitCount + 1);
    }


    public static function format(int $visitNumber): string
    {
        $locale = (string) locale();

        if (str_starts_with($locale, 'ms')) {
            return TreatmentReservationLang::trans('admin.crm.customer_visit_ordinal_ms', [
                'number' => $visitNumber,
            ]);
        }

        return TreatmentReservationLang::trans('admin.crm.customer_visit_ordinal', [
            'ordinal' => self::englishOrdinal($visitNumber),
        ]);
    }


    private static function englishOrdinal(int $number): string
    {
        $suffix = match ($number % 100) {
            11, 12, 13 => 'th',
            default => match ($number % 10) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            },
        };

        return $number . $suffix;
    }
}
