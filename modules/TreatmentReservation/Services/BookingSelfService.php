<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;

class BookingSelfService
{
    public const MAX_AVAILABILITY_RANGE_DAYS = 93;

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
        return $this->upcomingQuery()
            ->matchingCustomerPhone($normalizedPhone)
            ->get();
    }


    /**
     * Authenticated customers are scoped by order ownership, not by a phone
     * number that may have been reused or changed.
     *
     * @return Collection<int, TreatmentBooking>
     */
    public function upcomingForCustomer(User $customer): Collection
    {
        return $this->upcomingQuery()
            ->whereHas('order', function (Builder $order) use ($customer): void {
                $order->where('customer_id', $customer->getKey());
            })
            ->get();
    }


    public function findOwnedBooking(string $normalizedPhone, int $bookingId): ?TreatmentBooking
    {
        return $this->upcomingForPhone($normalizedPhone)
            ->firstWhere('id', $bookingId);
    }


    public function findOwnedBookingForCustomer(User $customer, int $bookingId): ?TreatmentBooking
    {
        return $this->upcomingForCustomer($customer)
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

            $this->assertSelfServiceMutable($lockedBooking);

            $lockedBooking->update(['status' => TreatmentBooking::STATUS_CANCELED]);
            $this->activityLogger->logStatusChange(
                $lockedBooking,
                $previousStatus,
                TreatmentBooking::STATUS_CANCELED
            );

            if ($lockedBooking->order_id) {
                $orderId = (int) $lockedBooking->order_id;

                $activeSiblings = TreatmentBooking::query()
                    ->where('order_id', $orderId)
                    ->whereKeyNot($lockedBooking->id)
                    ->whereNotIn('status', [TreatmentBooking::STATUS_CANCELED])
                    ->exists();

                if (! $activeSiblings) {
                    Order::query()
                        ->whereKey($orderId)
                        ->lockForUpdate()
                        ->first()
                        ?->update(['status' => Order::CANCELED]);
                } else {
                    $order = Order::query()->find($orderId);
                    if ($order) {
                        app(BookingSyncService::class)->refreshOrderAppointmentSnapshot($order);
                    }
                }
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
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay()
            ->min($start->copy()->addDays(self::MAX_AVAILABILITY_RANGE_DAYS));

        if ($productId && $spaBranchId && app('modules')->isEnabled('SpaBranch')) {
            return $this->appointmentAvailability->availableDates(
                $productId,
                $spaBranchId,
                $start->toDateString(),
                $end->toDateString(),
                $beauticianId ?: null,
                (int) $booking->id,
            );
        }

        if (! $beauticianId) {
            return [];
        }

        $dates = [];
        $cursor = $start;

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

            $this->assertSelfServiceMutable($lockedBooking);

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
                'customer_reminder_sent_at' => null,
                'customer_email_reminder_sent_at' => null,
                'schedule_status' => null,
            ]);

            if ($lockedBooking->order_id) {
                $order = Order::query()->whereKey($lockedBooking->order_id)->lockForUpdate()->first();

                if ($order) {
                    app(BookingSyncService::class)->refreshOrderAppointmentSnapshot($order);
                }
            }
        });
    }


    private function upcomingQuery(): Builder
    {
        return TreatmentBooking::query()
            ->withTreatmentProduct()
            ->with([
                'beautician.spaBranches',
                'product',
                'category',
                'order',
                'orderProduct.options.values',
                'orderProduct.variations.values',
            ])
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->where(function (Builder $query): void {
                $query->where(function (Builder $scheduled): void {
                    $scheduled->whereNotNull('appointment_date')
                        ->where('appointment_date', '>=', today()->toDateString());
                })->orWhere(function (Builder $tba): void {
                    $tba->where('schedule_status', TreatmentBooking::SCHEDULE_STATUS_TBA)
                        ->orWhere(function (Builder $legacy): void {
                            $legacy->whereNull('schedule_status')
                                ->whereNull('appointment_time');
                        });
                });
            })
            ->orderByRaw("CASE WHEN schedule_status = 'tba' OR appointment_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
    }


    private function assertSelfServiceMutable(TreatmentBooking $booking): void
    {
        if (! in_array($booking->status, [
            TreatmentBooking::STATUS_PENDING,
            TreatmentBooking::STATUS_IN_PROGRESS,
        ], true)) {
            throw new \InvalidArgumentException(
                trans('treatmentreservation::public.booking_action_not_allowed')
            );
        }
    }
}
