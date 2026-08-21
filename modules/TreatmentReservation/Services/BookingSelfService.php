<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class BookingSelfService
{
    public function __construct(
        private TreatmentBookingActivityLogger $activityLogger,
        private AppointmentAvailabilityService $appointmentAvailability,
        private BeauticianAvailabilityService $beauticianAvailability,
    ) {
    }


    /**
     * @return Collection<int, TreatmentBooking>
     */
    public function upcomingForPhone(string $normalizedPhone): Collection
    {
        return TreatmentBooking::query()
            ->withTreatmentProduct()
            ->with(['beautician', 'product', 'category', 'order'])
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


    /**
     * @return list<string>
     */
    public function availableSlotsForBooking(TreatmentBooking $booking, string $date): array
    {
        $beauticianId = (int) ($booking->beautician_id ?? 0);
        $productId = (int) ($booking->product_id ?? 0);
        $spaBranchId = (int) ($booking->spa_branch_id ?? $booking->order?->spa_branch_id ?? 0);

        if ($productId && $spaBranchId && app('modules')->isEnabled('SpaBranch')) {
            return $this->appointmentAvailability->availableSlots(
                $productId,
                $spaBranchId,
                $date,
                $beauticianId ?: null,
                $booking->id
            );
        }

        if (! $beauticianId) {
            return [];
        }

        return $this->beauticianAvailability->availableSlots($beauticianId, $date, $booking->id);
    }


    /**
     * @return list<string>
     */
    public function availableDatesForBooking(TreatmentBooking $booking, string $from, string $to): array
    {
        $beauticianId = (int) ($booking->beautician_id ?? 0);
        $productId = (int) ($booking->product_id ?? 0);
        $spaBranchId = (int) ($booking->spa_branch_id ?? $booking->order?->spa_branch_id ?? 0);

        if ($productId && $spaBranchId && app('modules')->isEnabled('SpaBranch')) {
            return $this->appointmentAvailability->availableDates(
                $productId,
                $spaBranchId,
                $from,
                $to,
                $beauticianId ?: null
            );
        }

        if (! $beauticianId) {
            return [];
        }

        $dates = [];
        $cursor = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        for (; $cursor->lte($end); $cursor->addDay()) {
            $date = $cursor->toDateString();

            if ($this->beauticianAvailability->availableSlots($beauticianId, $date, $booking->id) !== []) {
                $dates[] = $date;
            }
        }

        return $dates;
    }


    public function reschedule(TreatmentBooking $booking, string $date, string $time): void
    {
        DB::transaction(function () use ($booking, $date, $time): void {
            $lockedBooking = TreatmentBooking::query()
                ->with('order')
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedBooking->beautician_id) {
                throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
            }

            $productId = (int) ($lockedBooking->product_id ?? 0);
            $spaBranchId = (int) ($lockedBooking->spa_branch_id ?? $lockedBooking->order?->spa_branch_id ?? 0);

            if ($productId && $spaBranchId && app('modules')->isEnabled('SpaBranch')) {
                $this->appointmentAvailability->assertSlotBookable(
                    $productId,
                    $spaBranchId,
                    $date,
                    $time,
                    (int) $lockedBooking->beautician_id,
                    $lockedBooking->id
                );
            } else {
                $this->beauticianAvailability->lockAppointmentsForDate($lockedBooking->beautician_id, $date);

                if (! $this->beauticianAvailability->isSlotAvailable(
                    $lockedBooking->beautician_id,
                    $date,
                    $time,
                    $lockedBooking->id
                )) {
                    throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
                }
            }

            $normalizedTime = $this->appointmentAvailability->normalizeTime($time)
                ?? $this->beauticianAvailability->normalizeTime($time)
                ?? $time;

            $lockedBooking->update([
                'appointment_date' => $date,
                'appointment_time' => $normalizedTime,
                'schedule_status' => null,
            ]);

            if ($lockedBooking->order_id) {
                Order::query()
                    ->whereKey($lockedBooking->order_id)
                    ->lockForUpdate()
                    ->first()
                    ?->update([
                        'appointment_date' => $date,
                        'appointment_time' => $normalizedTime,
                        'schedule_status' => null,
                    ]);
            }
        });
    }
}
