<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\Product;
use Modules\TreatmentReservation\Entities\AppointmentDateOverride;
use Modules\TreatmentReservation\Entities\SpaBranchWeeklyAvailability;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Entities\TreatmentBranchAvailability;

/**
 * Single source of truth for treatment appointment availability.
 *
 * Priority:
 * 1. Specific date override (product-specific, then branch-wide)
 * 2. Treatment + Branch weekly schedule
 * 3. Branch weekly schedule
 * 4. Beautician working-hours fallback (legacy, when no DB schedule configured)
 *
 * TBA rows (null date/time) never consume capacity.
 */
class AppointmentAvailabilityService
{
    public const DEFAULT_DURATION_MINUTES = 60;

    public const DEFAULT_CAPACITY = 1;

    public function __construct(
        private BeauticianAvailabilityService $beauticianAvailability,
    ) {}


    /**
     * @return array{
     *     open: bool,
     *     source: string,
     *     times: list<string>,
     *     duration_minutes: int,
     *     capacity_per_slot: int,
     *     reason: ?string
     * }
     */
    public function resolveDaySchedule(int $productId, int $spaBranchId, string $date): array
    {
        $settings = $this->settingsFor($productId, $spaBranchId);
        $duration = $this->resolveDurationMinutes($productId, $spaBranchId, $settings);
        $capacity = $this->resolveCapacity($productId, $spaBranchId, $settings, null);

        if ($settings && ! $settings->is_bookable) {
            return $this->closedResult('treatment_disabled', $duration, $capacity);
        }

        $productOverride = $this->findOverride($spaBranchId, $productId, $date);
        $branchOverride = $this->findOverride($spaBranchId, AppointmentDateOverride::PRODUCT_ALL, $date);

        $treatmentWeekly = $this->treatmentWeeklyForDate($settings, $date);
        $branchWeekly = $this->branchWeeklyForDate($spaBranchId, $date);
        $weeklyFallbackTimes = $this->weeklyFallbackTimes($treatmentWeekly, $branchWeekly);

        if ($productOverride) {
            return $this->resultFromOverride(
                $productOverride,
                $duration,
                $capacity,
                'product_date_override',
                $weeklyFallbackTimes
            );
        }

        if ($branchOverride) {
            return $this->resultFromOverride(
                $branchOverride,
                $duration,
                $capacity,
                'branch_date_override',
                $weeklyFallbackTimes
            );
        }

        if ($treatmentWeekly !== null) {
            if (! $treatmentWeekly['is_open'] || $treatmentWeekly['times'] === []) {
                return $this->closedResult('treatment_weekly', $duration, $capacity);
            }

            return [
                'open' => true,
                'source' => 'treatment_weekly',
                'times' => $treatmentWeekly['times'],
                'duration_minutes' => $duration,
                'capacity_per_slot' => $capacity,
                'reason' => null,
            ];
        }

        if ($branchWeekly !== null) {
            if (! $branchWeekly['is_open'] || $branchWeekly['times'] === []) {
                return $this->closedResult('branch_weekly', $duration, $capacity);
            }

            return [
                'open' => true,
                'source' => 'branch_weekly',
                'times' => $branchWeekly['times'],
                'duration_minutes' => $duration,
                'capacity_per_slot' => $capacity,
                'reason' => null,
            ];
        }

        return [
            'open' => true,
            'source' => 'legacy_beautician',
            'times' => [],
            'duration_minutes' => $duration,
            'capacity_per_slot' => $capacity,
            'reason' => null,
        ];
    }


    /**
     * @return list<string> HH:mm slots still bookable
     */
    public function availableSlots(
        int $productId,
        int $spaBranchId,
        string $date,
        ?int $beauticianId = null,
        ?int $excludeBookingId = null,
        ?int $excludeOrderId = null,
    ): array {
        $day = $this->resolveDaySchedule($productId, $spaBranchId, $date);

        if (! $day['open']) {
            return [];
        }

        if ($day['source'] === 'legacy_beautician') {
            if (! $beauticianId) {
                return [];
            }

            return $this->beauticianAvailability->availableSlots($beauticianId, $date, $excludeBookingId);
        }

        $capacity = $day['capacity_per_slot'];
        $duration = $day['duration_minutes'];
        $slots = [];

        foreach ($day['times'] as $time) {
            $normalized = $this->beauticianAvailability->normalizeTime($time);

            if ($normalized === null) {
                continue;
            }

            if ($this->slotUsage($productId, $spaBranchId, $date, $normalized, $excludeBookingId, $excludeOrderId) >= $capacity) {
                continue;
            }

            if ($beauticianId && $this->beauticianHasConflict(
                $beauticianId,
                $date,
                $normalized,
                $duration,
                $excludeBookingId,
                $excludeOrderId
            )) {
                continue;
            }

            $slots[] = $normalized;
        }

        return $this->filterPastSlots($slots, $date);
    }


    /**
     * @return list<string> Y-m-d dates with at least one bookable slot
     */
    public function availableDates(
        int $productId,
        int $spaBranchId,
        string $from,
        string $to,
        ?int $beauticianId = null,
    ): array {
        $start = Carbon::parse($from)->startOfDay()->max(today()->startOfDay());
        $end = Carbon::parse($to)->startOfDay();
        $dates = [];

        for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addDay()) {
            $date = $cursor->toDateString();

            if ($this->availableSlots($productId, $spaBranchId, $date, $beauticianId) !== []) {
                $dates[] = $date;
            }
        }

        return $dates;
    }


    public function isSlotAvailable(
        int $productId,
        int $spaBranchId,
        string $date,
        string $time,
        ?int $beauticianId = null,
        ?int $excludeBookingId = null,
        ?int $excludeOrderId = null,
    ): bool {
        $normalized = $this->beauticianAvailability->normalizeTime($time);

        if ($normalized === null) {
            return false;
        }

        return in_array(
            $normalized,
            $this->availableSlots($productId, $spaBranchId, $date, $beauticianId, $excludeBookingId, $excludeOrderId),
            true
        );
    }


    public function assertSlotBookable(
        int $productId,
        int $spaBranchId,
        string $date,
        string $time,
        ?int $beauticianId = null,
        ?int $excludeBookingId = null,
        ?int $excludeOrderId = null,
    ): void {
        $this->lockSlotRows($productId, $spaBranchId, $date, $beauticianId);

        if (! $this->isSlotAvailable(
            $productId,
            $spaBranchId,
            $date,
            $time,
            $beauticianId,
            $excludeBookingId,
            $excludeOrderId
        )) {
            throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
        }
    }


    public function lockSlotRows(
        int $productId,
        int $spaBranchId,
        string $date,
        ?int $beauticianId = null,
    ): void {
        if (DB::transactionLevel() === 0) {
            return;
        }

        $now = now();
        $lockKeys = ["treatment:{$productId}:{$spaBranchId}:{$date}"];

        if ($beauticianId) {
            $lockKeys[] = "beautician:{$beauticianId}:{$date}";
        }

        sort($lockKeys);

        foreach ($lockKeys as $lockKey) {
            DB::table('appointment_availability_locks')->insertOrIgnore([
                'lock_key' => $lockKey,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('appointment_availability_locks')
                ->where('lock_key', $lockKey)
                ->lockForUpdate()
                ->first();
        }

        TreatmentBooking::query()
            ->where('product_id', $productId)
            ->where('spa_branch_id', $spaBranchId)
            ->where('appointment_date', $date)
            ->whereNotNull('appointment_time')
            ->whereIn('status', $this->beauticianAvailability->slotBlockingBookingStatuses())
            ->lockForUpdate()
            ->get();

        Order::query()
            ->where('spa_branch_id', $spaBranchId)
            ->where('appointment_date', $date)
            ->whereNotNull('appointment_time')
            ->whereIn('status', $this->beauticianAvailability->slotBlockingOrderStatuses())
            ->lockForUpdate()
            ->get();

        if ($beauticianId) {
            $this->beauticianAvailability->lockAppointmentsForDate($beauticianId, $date);
        }
    }


    public function resolveDurationMinutes(
        int $productId,
        int $spaBranchId,
        ?TreatmentBranchAvailability $settings = null,
        ?AppointmentDateOverride $override = null,
    ): int {
        if ($override?->duration_minutes) {
            return max(1, (int) $override->duration_minutes);
        }

        $settings ??= $this->settingsFor($productId, $spaBranchId);

        if ($settings?->duration_minutes) {
            return max(1, (int) $settings->duration_minutes);
        }

        $product = Product::withoutGlobalScope('active')->find($productId);

        if ($product) {
            $booking = new TreatmentBooking(['product_id' => $productId]);
            $booking->setRelation('product', $product);

            return max(1, $booking->resolveSlotDurationMinutes());
        }

        return self::DEFAULT_DURATION_MINUTES;
    }


    public function resolveCapacity(
        int $productId,
        int $spaBranchId,
        ?TreatmentBranchAvailability $settings = null,
        ?AppointmentDateOverride $override = null,
    ): int {
        if ($override?->capacity_per_slot) {
            return max(1, (int) $override->capacity_per_slot);
        }

        $settings ??= $this->settingsFor($productId, $spaBranchId);

        if ($settings?->capacity_per_slot) {
            return max(1, (int) $settings->capacity_per_slot);
        }

        return self::DEFAULT_CAPACITY;
    }


    public function settingsFor(int $productId, int $spaBranchId): ?TreatmentBranchAvailability
    {
        return TreatmentBranchAvailability::query()
            ->where('product_id', $productId)
            ->where('spa_branch_id', $spaBranchId)
            ->first();
    }


    public function hasConfiguredSchedule(int $productId, int $spaBranchId): bool
    {
        if ($this->settingsFor($productId, $spaBranchId)) {
            return true;
        }

        return SpaBranchWeeklyAvailability::query()
            ->where('spa_branch_id', $spaBranchId)
            ->exists();
    }


    public function normalizeTime(?string $time): ?string
    {
        return $this->beauticianAvailability->normalizeTime($time);
    }


    /**
     * @return array{is_open: bool, times: list<string>}|null
     */
    private function treatmentWeeklyForDate(?TreatmentBranchAvailability $settings, string $date): ?array
    {
        if (! $settings) {
            return null;
        }

        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $day = $settings->weeklyDays()
            ->with('slots')
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (! $day) {
            return ['is_open' => false, 'times' => []];
        }

        if (! $day->is_open) {
            return ['is_open' => false, 'times' => []];
        }

        return [
            'is_open' => true,
            'times' => $this->enabledTimes($day->slots),
        ];
    }


    /**
     * @return array{is_open: bool, times: list<string>}|null
     */
    private function branchWeeklyForDate(int $spaBranchId, string $date): ?array
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $day = SpaBranchWeeklyAvailability::query()
            ->with('slots')
            ->where('spa_branch_id', $spaBranchId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (! $day) {
            $any = SpaBranchWeeklyAvailability::query()
                ->where('spa_branch_id', $spaBranchId)
                ->exists();

            return $any ? ['is_open' => false, 'times' => []] : null;
        }

        if (! $day->is_open) {
            return ['is_open' => false, 'times' => []];
        }

        return [
            'is_open' => true,
            'times' => $this->enabledTimes($day->slots),
        ];
    }


    private function findOverride(int $spaBranchId, int $productId, string $date): ?AppointmentDateOverride
    {
        return AppointmentDateOverride::query()
            ->with('slots')
            ->where('spa_branch_id', $spaBranchId)
            ->where('product_id', $productId)
            ->whereDate('override_date', $date)
            ->first();
    }


    /**
     * @return array{open: bool, source: string, times: list<string>, duration_minutes: int, capacity_per_slot: int, reason: ?string}
     */
    private function resultFromOverride(
        AppointmentDateOverride $override,
        int $duration,
        int $capacity,
        string $source,
        array $weeklyFallbackTimes = [],
    ): array {
        if ($override->duration_minutes) {
            $duration = max(1, (int) $override->duration_minutes);
        }

        if ($override->capacity_per_slot) {
            $capacity = max(1, (int) $override->capacity_per_slot);
        }

        if ($override->status === AppointmentDateOverride::STATUS_CLOSED) {
            return $this->closedResult($source, $duration, $capacity, $override->reason);
        }

        $times = $this->enabledTimes($override->slots);

        // STATUS_OPEN with no explicit times means "follow weekly open hours".
        if ($times === [] && $override->status === AppointmentDateOverride::STATUS_OPEN) {
            $times = $weeklyFallbackTimes;
        }

        return [
            'open' => $times !== [],
            'source' => $source,
            'times' => $times,
            'duration_minutes' => $duration,
            'capacity_per_slot' => $capacity,
            'reason' => $override->reason,
        ];
    }


    /**
     * @param  array{is_open: bool, times: list<string>}|null  $treatmentWeekly
     * @param  array{is_open: bool, times: list<string>}|null  $branchWeekly
     * @return list<string>
     */
    private function weeklyFallbackTimes(?array $treatmentWeekly, ?array $branchWeekly): array
    {
        if ($treatmentWeekly && $treatmentWeekly['is_open'] && $treatmentWeekly['times'] !== []) {
            return $treatmentWeekly['times'];
        }

        if ($branchWeekly && $branchWeekly['is_open'] && $branchWeekly['times'] !== []) {
            return $branchWeekly['times'];
        }

        return [];
    }


    /**
     * @return array{open: bool, source: string, times: list<string>, duration_minutes: int, capacity_per_slot: int, reason: ?string}
     */
    private function closedResult(string $source, int $duration, int $capacity, ?string $reason = null): array
    {
        return [
            'open' => false,
            'source' => $source,
            'times' => [],
            'duration_minutes' => $duration,
            'capacity_per_slot' => $capacity,
            'reason' => $reason,
        ];
    }


    /**
     * @param Collection<int, object> $slots
     * @return list<string>
     */
    private function enabledTimes(Collection $slots): array
    {
        $times = [];

        foreach ($slots as $slot) {
            if (isset($slot->is_enabled) && ! $slot->is_enabled) {
                continue;
            }

            $normalized = $this->beauticianAvailability->normalizeTime((string) $slot->start_time);

            if ($normalized !== null) {
                $times[] = $normalized;
            }
        }

        $times = array_values(array_unique($times));
        sort($times);

        return $times;
    }


    private function slotUsage(
        int $productId,
        int $spaBranchId,
        string $date,
        string $time,
        ?int $excludeBookingId,
        ?int $excludeOrderId,
    ): int {
        $normalized = $this->beauticianAvailability->normalizeTime($time);

        if ($normalized === null) {
            return PHP_INT_MAX;
        }

        $excludeOrderId ??= $this->orderIdForBooking($excludeBookingId);

        $bookingCount = TreatmentBooking::query()
            ->where('product_id', $productId)
            ->where('spa_branch_id', $spaBranchId)
            ->where('appointment_date', $date)
            ->whereNotNull('appointment_time')
            ->whereIn('status', $this->beauticianAvailability->slotBlockingBookingStatuses())
            ->when($excludeBookingId, fn ($q) => $q->whereKeyNot($excludeBookingId))
            ->get(['appointment_time'])
            ->filter(fn ($row) => $this->beauticianAvailability->normalizeTime((string) $row->appointment_time) === $normalized)
            ->count();

        $orderIdsWithBooking = TreatmentBooking::query()
            ->whereNotNull('order_id')
            ->where('product_id', $productId)
            ->where('spa_branch_id', $spaBranchId)
            ->where('appointment_date', $date)
            ->pluck('order_id');

        $orphanOrders = Order::query()
            ->where('spa_branch_id', $spaBranchId)
            ->where('appointment_date', $date)
            ->whereNotNull('appointment_time')
            ->whereIn('status', $this->beauticianAvailability->slotBlockingOrderStatuses())
            ->when($excludeOrderId, fn ($q) => $q->whereKeyNot($excludeOrderId))
            ->when($orderIdsWithBooking->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $orderIdsWithBooking))
            ->whereHas('products', fn ($q) => $q->where('product_id', $productId))
            ->get(['appointment_time'])
            ->filter(fn ($row) => $this->beauticianAvailability->normalizeTime((string) $row->appointment_time) === $normalized)
            ->count();

        return $bookingCount + $orphanOrders;
    }


    private function beauticianHasConflict(
        int $beauticianId,
        string $date,
        string $startTime,
        int $durationMinutes,
        ?int $excludeBookingId,
        ?int $excludeOrderId,
    ): bool {
        $startMin = $this->minutesFromTime($startTime);
        $endMin = $startMin === null ? null : $startMin + max(1, $durationMinutes);

        if ($startMin === null || $endMin === null) {
            return true;
        }

        $excludeOrderId ??= $this->orderIdForBooking($excludeBookingId);

        $orders = Order::query()
            ->select(['id', 'appointment_time'])
            ->where('beautician_id', $beauticianId)
            ->where('appointment_date', $date)
            ->whereNotNull('appointment_time')
            ->whereIn('status', $this->beauticianAvailability->slotBlockingOrderStatuses())
            ->when($excludeOrderId, fn ($q) => $q->whereKeyNot($excludeOrderId))
            ->get();

        $bookings = TreatmentBooking::query()
            ->select(['id', 'order_id', 'appointment_time', 'product_id', 'spa_branch_id', 'duration_minutes_snapshot'])
            ->with(['product.attributes.attribute', 'product.attributes.values.attributeValue'])
            ->where('beautician_id', $beauticianId)
            ->where('appointment_date', $date)
            ->whereNotNull('appointment_time')
            ->whereIn('status', $this->beauticianAvailability->slotBlockingBookingStatuses())
            ->when($excludeBookingId, fn ($q) => $q->whereKeyNot($excludeBookingId))
            ->when($excludeOrderId, fn ($q) => $q->where(function ($nested) use ($excludeOrderId) {
                $nested->whereNull('order_id')->orWhere('order_id', '!=', $excludeOrderId);
            }))
            ->get();

        foreach ($orders as $order) {
            $otherStart = $this->minutesFromTime((string) $order->appointment_time);

            if ($otherStart === null) {
                continue;
            }

            $otherEnd = $otherStart + BeauticianAvailabilityService::SLOT_MINUTES;

            if ($startMin < $otherEnd && $endMin > $otherStart) {
                return true;
            }
        }

        foreach ($bookings as $booking) {
            $otherStart = $this->minutesFromTime((string) $booking->appointment_time);

            if ($otherStart === null) {
                continue;
            }

            $otherEnd = $otherStart + max(1, $this->durationMinutesForBooking($booking));

            if ($startMin < $otherEnd && $endMin > $otherStart) {
                return true;
            }
        }

        return $this->beauticianOutsideWindowOrBlocked($beauticianId, $date, $startMin, $endMin);
    }


    private function beauticianOutsideWindowOrBlocked(
        int $beauticianId,
        string $date,
        int $startMin,
        int $endMin,
    ): bool {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $hours = $this->beauticianAvailability->workingHoursFor($beauticianId)
            ->firstWhere('day_of_week', $dayOfWeek);

        if (! $hours) {
            return true;
        }

        $windowStart = $this->minutesFromTime((string) $hours->start_time);
        $windowEnd = $this->minutesFromTime((string) $hours->end_time);

        if ($windowStart === null || $windowEnd === null) {
            return true;
        }

        if ($startMin < $windowStart || $endMin > $windowEnd) {
            return true;
        }

        return $this->beauticianAvailability->isTimeRangeBlocked(
            $beauticianId,
            $date,
            $this->timeFromMinutes($startMin),
            $this->timeFromMinutes($endMin),
        );
    }


    private function durationMinutesForBooking(TreatmentBooking $booking): int
    {
        if ((int) ($booking->duration_minutes_snapshot ?? 0) > 0) {
            return (int) $booking->duration_minutes_snapshot;
        }

        $productId = (int) ($booking->product_id ?? 0);
        $spaBranchId = (int) ($booking->spa_branch_id ?? 0);

        if ($productId > 0 && $spaBranchId > 0) {
            return $this->resolveDurationMinutes($productId, $spaBranchId);
        }

        return max(1, $booking->resolveSlotDurationMinutes());
    }


    private function orderIdForBooking(?int $bookingId): ?int
    {
        if (! $bookingId) {
            return null;
        }

        $orderId = TreatmentBooking::query()->whereKey($bookingId)->value('order_id');

        return $orderId ? (int) $orderId : null;
    }


    /**
     * @param list<string> $slots
     * @return list<string>
     */
    private function filterPastSlots(array $slots, string $date): array
    {
        $slotDate = Carbon::parse($date)->startOfDay();

        if ($slotDate->isPast() && ! $slotDate->isToday()) {
            return [];
        }

        if (! $slotDate->isToday()) {
            return $slots;
        }

        $nowMinutes = (now()->hour * 60) + now()->minute;

        return array_values(array_filter(
            $slots,
            fn (string $slot) => ($this->minutesFromTime($slot) ?? 0) > $nowMinutes
        ));
    }


    private function minutesFromTime(string $time): ?int
    {
        $normalized = $this->beauticianAvailability->normalizeTime($time);

        if ($normalized === null) {
            return null;
        }

        [$hour, $minute] = array_map('intval', explode(':', $normalized));

        return ($hour * 60) + $minute;
    }

    private function timeFromMinutes(int $minutes): string
    {
        $minutes = max(0, min((24 * 60) - 1, $minutes));

        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
