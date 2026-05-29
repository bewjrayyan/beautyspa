<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Collection;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class BookingSelfService
{
    public function __construct(
        private TreatmentBookingActivityLogger $activityLogger,
        private BeauticianAvailabilityService $availability
    ) {}


    /**
     * @return Collection<int, TreatmentBooking>
     */
    public function upcomingForPhone(string $normalizedPhone): Collection
    {
        return TreatmentBooking::query()
            ->withTreatmentProduct()
            ->with(['beautician', 'product', 'category'])
            ->matchingCustomerPhone($normalizedPhone)
            ->whereNotNull('appointment_date')
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->whereDate('appointment_date', '>=', today())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();
    }


    public function findOwnedBooking(string $normalizedPhone, int $bookingId): ?TreatmentBooking
    {
        return $this->upcomingForPhone($normalizedPhone)
            ->firstWhere('id', $bookingId);
    }


    public function cancel(TreatmentBooking $booking): void
    {
        $previousStatus = $booking->status;

        $booking->update(['status' => TreatmentBooking::STATUS_CANCELED]);

        $this->activityLogger->logStatusChange($booking, $previousStatus, TreatmentBooking::STATUS_CANCELED);

        if ($booking->order_id) {
            $booking->order?->update(['status' => Order::CANCELED]);
        }
    }


    public function reschedule(TreatmentBooking $booking, string $date, string $time): void
    {
        if (! $booking->beautician_id) {
            throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
        }

        if (! $this->availability->isSlotAvailable($booking->beautician_id, $date, $time, $booking->id)) {
            throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
        }

        $normalizedTime = $this->availability->normalizeTime($time) ?? $time;

        $booking->update([
            'appointment_date' => $date,
            'appointment_time' => $normalizedTime,
        ]);

        if ($booking->order_id && $booking->order) {
            $booking->order->update([
                'appointment_date' => $date,
                'appointment_time' => $normalizedTime,
            ]);
        }
    }
}
