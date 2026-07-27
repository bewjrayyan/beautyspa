<?php

namespace Modules\Account\Services;

use Modules\Account\Entities\ConsultationSubmission;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class ConsultationContextService
{
    /** @return array<string, mixed> */
    public function capture(TreatmentBooking $booking): array
    {
        $booking->loadMissing([
            'product',
            'beautician.spaBranches',
            'order.spaBranch',
        ]);

        return [
            'treatment_name' => $booking->product?->name,
            'appointment_date' => $booking->appointment_date?->toDateString(),
            'appointment_time' => $booking->appointmentTimeRange(),
            'branch_name' => $booking->order?->spaBranch?->name ?: $booking->spaBranchLabel(),
            'beautician_name' => $booking->beautician?->name,
            'order_id' => $booking->order_id,
        ];
    }

    /** @return array<string, mixed> */
    public function forDisplay(ConsultationSubmission $submission): array
    {
        if (is_array($submission->context_snapshot)) {
            return $submission->context_snapshot;
        }

        $booking = $submission->treatmentBooking;
        $order = $submission->order;
        $orderProduct = $submission->orderProduct;

        return [
            'treatment_name' => $orderProduct?->nameWithSelections()
                ?: $booking?->product?->name
                ?: $submission->product?->name,
            'appointment_date' => ($booking?->appointment_date ?: $order?->appointment_date)?->toDateString(),
            'appointment_time' => $booking?->appointmentTimeRange() ?: $order?->appointment_time,
            'branch_name' => $order?->spaBranch?->name ?: $booking?->spaBranchLabel(),
            'beautician_name' => $submission->beautician?->name
                ?: $booking?->beautician?->name
                ?: $order?->beautician?->name,
            'order_id' => $submission->order_id,
        ];
    }
}
