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

        $orderStatus = match ($jobSheetStatus) {
            TreatmentBooking::STATUS_COMPLETED => Order::COMPLETED,
            TreatmentBooking::STATUS_IN_PROGRESS, TreatmentBooking::STATUS_PENDING => Order::PROCESSING,
            default => null,
        };

        if ($orderStatus === null || $order->status === $orderStatus) {
            return;
        }

        $order->update(['status' => $orderStatus]);
    }
}
