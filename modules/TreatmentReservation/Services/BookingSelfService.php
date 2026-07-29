<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
            ->where('appointment_date', '>=', today()->toDateString())
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
        DB::transaction(function () use ($booking): void {
            $lockedBooking = TreatmentBooking::query()
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();
            $previousStatus = $lockedBooking->status;

            if ($previousStatus === TreatmentBooking::STATUS_CANCELED) {
                return;
            }

            $lockedBooking->update(['status' => TreatmentBooking::STATUS_CANCELED]);
            $this->activityLogger->logStatusChange(
                $lockedBooking,
                $previousStatus,
                TreatmentBooking::STATUS_CANCELED
            );

            if ($lockedBooking->order_id) {
                Order::query()
                    ->whereKey($lockedBooking->order_id)
                    ->lockForUpdate()
                    ->first()
                    ?->update(['status' => Order::CANCELED]);
            }
        });
    }


    public function reschedule(TreatmentBooking $booking, string $date, string $time): void
    {
        DB::transaction(function () use ($booking, $date, $time): void {
            $lockedBooking = TreatmentBooking::query()
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedBooking->beautician_id) {
                throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
            }

            $this->availability->lockAppointmentsForDate($lockedBooking->beautician_id, $date);

            if (! $this->availability->isSlotAvailable(
                $lockedBooking->beautician_id,
                $date,
                $time,
                $lockedBooking->id
            )) {
                throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
            }

            $normalizedTime = $this->availability->normalizeTime($time) ?? $time;
            $lockedBooking->update([
                'appointment_date' => $date,
                'appointment_time' => $normalizedTime,
            ]);

            if ($lockedBooking->order_id) {
                Order::query()
                    ->whereKey($lockedBooking->order_id)
                    ->lockForUpdate()
                    ->first()
                    ?->update([
                        'appointment_date' => $date,
                        'appointment_time' => $normalizedTime,
                    ]);
            }
        });
    }
}
