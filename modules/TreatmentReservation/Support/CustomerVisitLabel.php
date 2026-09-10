<?php

namespace Modules\TreatmentReservation\Support;

use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Support\PhoneNumber;

class CustomerVisitLabel
{
    public static function forBooking(TreatmentBooking $booking, int $completedVisitCount): string
    {
        return self::format(self::visitNumber($booking, $completedVisitCount));
    }


    public static function forOrder(Order $order): ?string
    {
        $booking = self::primaryBookingForOrder($order);

        if (! $booking) {
            return null;
        }

        return self::forBooking($booking, self::completedVisitCountForBooking($booking));
    }


    public static function completedVisitCountForOrder(Order $order): int
    {
        $booking = self::primaryBookingForOrder($order);

        if (! $booking) {
            return 0;
        }

        return self::completedVisitCountForBooking($booking);
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


    private static function primaryBookingForOrder(Order $order): ?TreatmentBooking
    {
        if ($order->relationLoaded('treatmentBookings') && $order->treatmentBookings->isNotEmpty()) {
            return $order->treatmentBookings->first();
        }

        if ($order->relationLoaded('treatmentBooking') && $order->treatmentBooking) {
            return $order->treatmentBooking;
        }

        return TreatmentBooking::query()
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->first();
    }


    private static function completedVisitCountForBooking(TreatmentBooking $booking): int
    {
        $phone = PhoneNumber::normalize((string) ($booking->customer_phone ?? ''));

        if ($phone === '') {
            return $booking->status === TreatmentBooking::STATUS_COMPLETED ? 1 : 0;
        }

        return TreatmentBooking::query()
            ->matchingCustomerPhone($phone)
            ->where('status', TreatmentBooking::STATUS_COMPLETED)
            ->count();
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
