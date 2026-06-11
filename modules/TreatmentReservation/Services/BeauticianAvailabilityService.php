<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\BeauticianBlockedTime;
use Modules\TreatmentReservation\Entities\BeauticianWorkingHour;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class BeauticianAvailabilityService
{
    public const SLOT_MINUTES = 60;

    /**
     * @return array<int, array{day_of_week: int, start_time: string, end_time: string}>
     */
    public function defaultWorkingHoursTemplate(): array
    {
        $hours = [];

        foreach ([1, 2, 3, 4, 5, 6] as $day) {
            $hours[] = [
                'day_of_week' => $day,
                'start_time' => '10:00',
                'end_time' => '18:00',
            ];
        }

        return $hours;
    }


    /**
     * @return Collection<int, BeauticianWorkingHour>
     */
    public function workingHoursFor(int $beauticianId): Collection
    {
        $hours = BeauticianWorkingHour::query()
            ->where('beautician_id', $beauticianId)
            ->orderBy('day_of_week')
            ->get();

        if ($hours->isEmpty()) {
            return collect($this->defaultWorkingHoursTemplate())
                ->map(fn (array $row) => new BeauticianWorkingHour($row));
        }

        return $hours;
    }


    /**
     * @param array<int, array{day_of_week: int, start_time: string, end_time: string, enabled?: bool}> $rules
     */
    public function syncWorkingHours(int $beauticianId, array $rules): void
    {
        BeauticianWorkingHour::query()->where('beautician_id', $beauticianId)->delete();

        foreach ($rules as $rule) {
            if (empty($rule['enabled'])) {
                continue;
            }

            $start = $this->normalizeTime($rule['start_time'] ?? '');
            $end = $this->normalizeTime($rule['end_time'] ?? '');

            if ($start === null || $end === null || $start >= $end) {
                continue;
            }

            BeauticianWorkingHour::create([
                'beautician_id' => $beauticianId,
                'day_of_week' => (int) $rule['day_of_week'],
                'start_time' => $start,
                'end_time' => $end,
            ]);
        }
    }


    public function addBlockedTime(
        int $beauticianId,
        string $date,
        string $startTime,
        string $endTime,
        ?string $note = null
    ): BeauticianBlockedTime {
        $start = $this->normalizeTime($startTime);
        $end = $this->normalizeTime($endTime);

        if ($start === null || $end === null || $start >= $end) {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.availability.invalid_block'));
        }

        return BeauticianBlockedTime::create([
            'beautician_id' => $beauticianId,
            'block_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'note' => $note,
        ]);
    }


    public function removeBlockedTime(int $beauticianId, int $blockId): void
    {
        BeauticianBlockedTime::query()
            ->where('beautician_id', $beauticianId)
            ->where('id', $blockId)
            ->delete();
    }


    /**
     * @return Collection<int, BeauticianBlockedTime>
     */
    public function upcomingBlocksFor(int $beauticianId): Collection
    {
        return BeauticianBlockedTime::query()
            ->where('beautician_id', $beauticianId)
            ->whereDate('block_date', '>=', today())
            ->orderBy('block_date')
            ->orderBy('start_time')
            ->limit(30)
            ->get();
    }


    /**
     * @return array<int, string>
     */
    public function availableSlots(int $beauticianId, string $date, ?int $excludeBookingId = null): array
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $hours = $this->workingHoursFor($beauticianId)
            ->firstWhere('day_of_week', $dayOfWeek);

        if (! $hours) {
            return [];
        }

        $windowStart = $this->minutesFromTime($hours->start_time);
        $windowEnd = $this->minutesFromTime($hours->end_time);

        if ($windowStart === null || $windowEnd === null || $windowStart >= $windowEnd) {
            return [];
        }

        $slots = [];

        for ($minute = $windowStart; $minute + self::SLOT_MINUTES <= $windowEnd; $minute += self::SLOT_MINUTES) {
            $start = $this->timeFromMinutes($minute);
            $end = $this->timeFromMinutes($minute + self::SLOT_MINUTES);

            if ($this->isBlocked($beauticianId, $date, $start, $end)) {
                continue;
            }

            if ($this->hasBookingConflict($beauticianId, $date, $start, $end, $excludeBookingId)) {
                continue;
            }

            $slots[] = $start;
        }

        return $this->filterPastSlots($slots, $date);
    }


    /**
     * @return array<int, string>
     */
    public function slotBlockingOrderStatuses(): array
    {
        return [
            Order::PENDING_PAYMENT,
            Order::PENDING,
            Order::PROCESSING,
            Order::ON_HOLD,
            Order::COMPLETED,
        ];
    }


    /**
     * @return array<int, string>
     */
    public function slotBlockingBookingStatuses(): array
    {
        return [
            TreatmentBooking::STATUS_PENDING,
            TreatmentBooking::STATUS_IN_PROGRESS,
        ];
    }


    /**
     * Lock existing appointments for a beautician on a date (call inside a DB transaction).
     */
    public function lockAppointmentsForDate(int $beauticianId, string $date): void
    {
        if (DB::transactionLevel() === 0) {
            return;
        }

        TreatmentBooking::query()
            ->where('beautician_id', $beauticianId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', $this->slotBlockingBookingStatuses())
            ->lockForUpdate()
            ->get();

        Order::query()
            ->where('beautician_id', $beauticianId)
            ->whereDate('appointment_date', $date)
            ->whereNotNull('appointment_time')
            ->whereIn('status', $this->slotBlockingOrderStatuses())
            ->lockForUpdate()
            ->get();
    }


    public function isSlotAvailable(
        int $beauticianId,
        string $date,
        string $time,
        ?int $excludeBookingId = null
    ): bool {
        $normalized = $this->normalizeTime($time);

        if ($normalized === null) {
            return false;
        }

        return in_array($normalized, $this->availableSlots($beauticianId, $date, $excludeBookingId), true);
    }


    private function isBlocked(int $beauticianId, string $date, string $start, string $end): bool
    {
        $startMin = $this->minutesFromTime($start);
        $endMin = $this->minutesFromTime($end);

        if ($startMin === null || $endMin === null) {
            return true;
        }

        $blocks = BeauticianBlockedTime::query()
            ->where('beautician_id', $beauticianId)
            ->whereDate('block_date', $date)
            ->get();

        foreach ($blocks as $block) {
            $blockStart = $this->minutesFromTime($block->start_time);
            $blockEnd = $this->minutesFromTime($block->end_time);

            if ($blockStart === null || $blockEnd === null) {
                continue;
            }

            if ($startMin < $blockEnd && $endMin > $blockStart) {
                return true;
            }
        }

        return false;
    }


    private function hasBookingConflict(
        int $beauticianId,
        string $date,
        string $start,
        string $end,
        ?int $excludeBookingId
    ): bool {
        $startMin = $this->minutesFromTime($start);
        $endMin = $this->minutesFromTime($end);

        if ($startMin === null || $endMin === null) {
            return true;
        }

        $excludeOrderId = $this->excludedOrderIdForBooking($excludeBookingId);

        $orders = Order::query()
            ->where('beautician_id', $beauticianId)
            ->whereDate('appointment_date', $date)
            ->whereNotNull('appointment_time')
            ->whereIn('status', $this->slotBlockingOrderStatuses())
            ->when($excludeOrderId, fn ($q) => $q->where('id', '!=', $excludeOrderId))
            ->get();

        foreach ($orders as $order) {
            if ($this->intervalsOverlap($startMin, $endMin, $order->appointment_time)) {
                return true;
            }
        }

        $bookings = TreatmentBooking::query()
            ->where('beautician_id', $beauticianId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', $this->slotBlockingBookingStatuses())
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->when($excludeOrderId, fn ($q) => $q->where(function ($query) use ($excludeOrderId) {
                $query->whereNull('order_id')
                    ->orWhere('order_id', '!=', $excludeOrderId);
            }))
            ->get();

        foreach ($bookings as $booking) {
            if ($this->intervalsOverlap($startMin, $endMin, $booking->appointment_time)) {
                return true;
            }
        }

        return false;
    }


    private function excludedOrderIdForBooking(?int $excludeBookingId): ?int
    {
        if (! $excludeBookingId) {
            return null;
        }

        $orderId = TreatmentBooking::query()
            ->where('id', $excludeBookingId)
            ->value('order_id');

        return $orderId ? (int) $orderId : null;
    }


    private function intervalsOverlap(int $startMin, int $endMin, mixed $appointmentTime): bool
    {
        $bookingStart = $this->minutesFromTime($appointmentTime);

        if ($bookingStart === null) {
            return false;
        }

        $bookingEnd = $bookingStart + self::SLOT_MINUTES;

        return $startMin < $bookingEnd && $endMin > $bookingStart;
    }


    /**
     * @param array<int, string> $slots
     * @return array<int, string>
     */
    private function filterPastSlots(array $slots, string $date): array
    {
        if (! Carbon::parse($date)->isToday()) {
            return $slots;
        }

        $nowMinutes = (now()->hour * 60) + now()->minute;

        return array_values(array_filter(
            $slots,
            fn (string $slot) => ($this->minutesFromTime($slot) ?? 0) > $nowMinutes
        ));
    }


    public function normalizeTime(?string $time): ?string
    {
        if ($time === null || trim($time) === '') {
            return null;
        }

        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }


    private function minutesFromTime(mixed $time): ?int
    {
        $normalized = $this->normalizeTime(is_string($time) ? $time : (string) $time);

        if ($normalized === null) {
            return null;
        }

        [$hour, $minute] = array_map('intval', explode(':', $normalized));

        return ($hour * 60) + $minute;
    }


    private function timeFromMinutes(int $minutes): string
    {
        $hours = (int) floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }
}
