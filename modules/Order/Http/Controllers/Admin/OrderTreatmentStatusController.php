<?php

namespace Modules\Order\Http\Controllers\Admin;

use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Events\OrderUpdated;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\TreatmentBookingActivityLogger;

class OrderTreatmentStatusController
{
    public function update(Order $order): string
    {
        $status = (string) request('treatment_status');

        if (! in_array($status, TreatmentBooking::statuses(), true)) {
            abort(422, trans('order::messages.invalid_treatment_status'));
        }

        $booking = TreatmentBooking::query()
            ->where('order_id', $order->id)
            ->firstOrFail();

        $previousStatus = $booking->status;

        if ($previousStatus === $status) {
            return trans('order::messages.treatment_status_updated');
        }

        $booking->update(['status' => $status]);

        app(TreatmentBookingActivityLogger::class)->logStatusChange(
            $booking,
            $previousStatus,
            $status
        );

        $freshOrder = $order->fresh(['treatmentBookings', 'beautician']);

        event(new OrderUpdated($freshOrder));
        event(new OrderStatusChanged($freshOrder, 'treatment', $previousStatus, $status));

        return trans('order::messages.treatment_status_updated');
    }
}
