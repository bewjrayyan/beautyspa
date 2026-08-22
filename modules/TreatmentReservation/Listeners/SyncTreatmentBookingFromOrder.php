<?php

namespace Modules\TreatmentReservation\Listeners;

use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\BookingSyncService;

class SyncTreatmentBookingFromOrder
{
    public function __construct(private BookingSyncService $sync) {}


    public function handleOrderStatusChanged(OrderStatusChanged $event): void
    {
        $order = $event->order;

        if (
            BookingSyncService::shouldDeferUntilPayment($order)
            && $order->treatmentBookings()->count() === 0
            && (
                (is_array($order->checkout_treatment_lines) && $order->checkout_treatment_lines !== [])
                || BookingSyncService::resolvePendingCheckoutLines($order) !== null
            )
        ) {
            return;
        }

        if ($order->wasChanged('status')) {
            TreatmentBooking::query()
                ->where('order_id', $order->id)
                ->each(fn (TreatmentBooking $booking) => $this->applyJobSheetStatusFromOrder($booking, $order));
        }

        $this->sync->syncFromOrder($order);
    }


    private function applyJobSheetStatusFromOrder(TreatmentBooking $booking, Order $order): void
    {
        $jobSheetStatus = match ($order->status) {
            Order::CANCELED, Order::REFUNDED => TreatmentBooking::STATUS_CANCELED,
            default => null,
        };

        if ($jobSheetStatus === null || $booking->status === $jobSheetStatus) {
            return;
        }

        if (
            $booking->status === TreatmentBooking::STATUS_COMPLETED
            && $jobSheetStatus !== TreatmentBooking::STATUS_CANCELED
        ) {
            return;
        }

        $booking->update(['status' => $jobSheetStatus]);
    }
}
