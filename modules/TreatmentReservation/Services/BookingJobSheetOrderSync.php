<?php

namespace Modules\TreatmentReservation\Services;

use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class BookingJobSheetOrderSync
{
    /**
     * Keep the linked sales order aligned when staff move a card on the job sheet.
     */
    public function syncOrderStatus(TreatmentBooking $booking, string $jobSheetStatus): void
    {
        if (! $booking->order_id) {
            return;
        }

        $order = $booking->order;

        if (! $order) {
            return;
        }

        if ($jobSheetStatus === TreatmentBooking::STATUS_CANCELED) {
            $hasActive = TreatmentBooking::query()
                ->where('order_id', $booking->order_id)
                ->whereNotIn('status', [TreatmentBooking::STATUS_CANCELED])
                ->exists();

            if (! $hasActive && $order->status !== Order::CANCELED) {
                $order->update(['status' => Order::CANCELED]);
            }

            return;
        }

        if ($jobSheetStatus !== TreatmentBooking::STATUS_COMPLETED) {
            return;
        }

        $incomplete = TreatmentBooking::query()
            ->where('order_id', $booking->order_id)
            ->whereNotIn('status', [
                TreatmentBooking::STATUS_COMPLETED,
                TreatmentBooking::STATUS_CANCELED,
            ])
            ->exists();

        if ($incomplete || $order->status === Order::COMPLETED) {
            return;
        }

        $order->update(['status' => Order::COMPLETED]);
    }
}
