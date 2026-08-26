<?php

namespace Modules\TreatmentReservation\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\BookingSyncService;

class SyncTreatmentBookingFromOrder implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [15, 60, 180];

    public function __construct(private BookingSyncService $sync) {}

    public function handleOrderStatusChanged(OrderStatusChanged $event): void
    {
        // Treatment status was updated on the booking directly — do not re-sync
        // from order (avoids redundant jobs and accidental status overwrite).
        if (($event->changeType ?: 'order') === 'treatment') {
            return;
        }

        $order = $event->order->fresh() ?? $event->order;

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

        // Do not rely on wasChanged() — queued models are reloaded without dirty state.
        // applyJobSheetStatusFromOrder is idempotent for non-cancel statuses.
        TreatmentBooking::query()
            ->where('order_id', $order->id)
            ->each(fn (TreatmentBooking $booking) => $this->applyJobSheetStatusFromOrder($booking, $order));

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
