<?php

namespace Modules\Order\Http\Controllers\Admin;

use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderUpdated;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\TreatmentBookingActivityLogger;

class OrderTreatmentStatusController
{
    public function update(Order $order): string
    {
        $status = (string) request('treatment_status');

        if (! in_array($status, TreatmentBooking::statuses(), true)) {
            abort(422, 'Invalid treatment status.');
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

        event(new OrderUpdated($order->fresh()));

        return trans('order::messages.treatment_status_updated');
    }
}
