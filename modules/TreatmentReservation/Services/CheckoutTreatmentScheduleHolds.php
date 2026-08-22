<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Http\Request;

/**
 * Temporary holds from sibling treatment lines in the same checkout request.
 */
class CheckoutTreatmentScheduleHolds
{
    /**
     * @return list<array{
     *     beautician_id: int,
     *     appointment_date: string,
     *     appointment_time: string,
     *     product_id: int,
     *     duration_minutes: int
     * }>
     */
    public static function fromRequest(Request $request, ?int $excludeIndex = null): array
    {
        if (! $request->filled('treatment_bookings') || ! is_array($request->input('treatment_bookings'))) {
            return [];
        }

        $spaBranchId = (int) $request->input('spa_branch_id');
        $availability = app(AppointmentAvailabilityService::class);
        $normalize = app(BeauticianAvailabilityService::class);
        $holds = [];

        foreach ($request->input('treatment_bookings') as $index => $line) {
            if ($excludeIndex !== null && (int) $index === $excludeIndex) {
                continue;
            }

            if (! is_array($line) || filter_var($line['schedule_later'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            $beauticianId = (int) ($line['beautician_id'] ?? 0);
            $date = (string) ($line['appointment_date'] ?? '');
            $time = $normalize->normalizeTime((string) ($line['appointment_time'] ?? ''));
            $productId = (int) ($line['product_id'] ?? 0);

            if (! $beauticianId || $date === '' || $time === null) {
                continue;
            }

            $duration = BeauticianAvailabilityService::SLOT_MINUTES;

            if ($productId > 0 && $spaBranchId > 0) {
                $duration = $availability->resolveDurationMinutes($productId, $spaBranchId);
            }

            $holds[] = [
                'beautician_id' => $beauticianId,
                'appointment_date' => $date,
                'appointment_time' => $time,
                'product_id' => $productId,
                'duration_minutes' => max(1, $duration),
            ];
        }

        return $holds;
    }


    /**
     * @param  list<array<string, mixed>>  $holds
     */
    public static function slotConflictsWithHolds(
        int $beauticianId,
        string $date,
        string $startTime,
        int $durationMinutes,
        array $holds,
    ): bool {
        if ($holds === []) {
            return false;
        }

        $normalize = app(BeauticianAvailabilityService::class);
        $normalized = $normalize->normalizeTime($startTime);

        if ($normalized === null) {
            return true;
        }

        $startMin = self::minutes($normalized);
        $endMin = $startMin + max(1, $durationMinutes);

        foreach ($holds as $hold) {
            if ((int) ($hold['beautician_id'] ?? 0) !== $beauticianId) {
                continue;
            }

            if ((string) ($hold['appointment_date'] ?? '') !== $date) {
                continue;
            }

            $holdStart = $normalize->normalizeTime((string) ($hold['appointment_time'] ?? ''));

            if ($holdStart === null) {
                continue;
            }

            $holdStartMin = self::minutes($holdStart);
            $holdEndMin = $holdStartMin + max(1, (int) ($hold['duration_minutes'] ?? BeauticianAvailabilityService::SLOT_MINUTES));

            if ($startMin < $holdEndMin && $endMin > $holdStartMin) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param  list<array{time: string, status: string}>  $slotOptions
     * @param  list<array<string, mixed>>  $holds
     * @return list<array{time: string, status: string}>
     */
    public static function applyToSlotOptions(
        array $slotOptions,
        int $beauticianId,
        string $date,
        int $durationMinutes,
        array $holds,
    ): array {
        if ($holds === [] || $slotOptions === []) {
            return $slotOptions;
        }

        return array_map(function (array $option) use ($beauticianId, $date, $durationMinutes, $holds) {
            if (($option['status'] ?? '') !== 'available') {
                return $option;
            }

            if (self::slotConflictsWithHolds(
                $beauticianId,
                $date,
                (string) $option['time'],
                $durationMinutes,
                $holds
            )) {
                return ['time' => $option['time'], 'status' => 'booked'];
            }

            return $option;
        }, $slotOptions);
    }


    private static function minutes(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return ($hour * 60) + $minute;
    }
}
