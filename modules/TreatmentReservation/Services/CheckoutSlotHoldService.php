<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\DB;
use Modules\Order\Entities\Order;

/**
 * Short-lived slot holds while online checkout payment (e.g. FPX) is in progress.
 */
class CheckoutSlotHoldService
{
    public const HOLD_MINUTES = 30;

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function placeHoldsForOrder(Order $order, array $lines): void
    {
        if (! BookingSyncService::shouldDeferUntilPayment($order) || $lines === []) {
            return;
        }

        $this->releaseHoldsForOrder((int) $order->id);

        $spaBranchId = (int) ($order->spa_branch_id ?? 0);
        $availability = app(AppointmentAvailabilityService::class);
        $expiresAt = now()->addMinutes(self::HOLD_MINUTES);
        $rows = [];

        foreach ($lines as $line) {
            if (
                ! is_array($line)
                || filter_var($line['schedule_later'] ?? false, FILTER_VALIDATE_BOOLEAN)
                || empty($line['beautician_id'])
                || empty($line['appointment_date'])
                || empty($line['appointment_time'])
            ) {
                continue;
            }

            $time = $availability->normalizeTime((string) $line['appointment_time']);

            if ($time === null) {
                continue;
            }

            $productId = (int) ($line['product_id'] ?? 0);
            $duration = ($productId > 0 && $spaBranchId > 0)
                ? $availability->resolveDurationMinutes($productId, $spaBranchId)
                : BeauticianAvailabilityService::SLOT_MINUTES;

            $rows[] = [
                'order_id' => (int) $order->id,
                'beautician_id' => (int) $line['beautician_id'],
                'appointment_date' => (string) $line['appointment_date'],
                'appointment_time' => $time,
                'product_id' => $productId > 0 ? $productId : null,
                'spa_branch_id' => $spaBranchId > 0 ? $spaBranchId : null,
                'duration_minutes' => max(1, $duration),
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows !== []) {
            DB::table('treatment_checkout_slot_holds')->insert($rows);
        }
    }


    public function extendHoldsForOrder(int $orderId): void
    {
        if ($orderId < 1) {
            return;
        }

        DB::table('treatment_checkout_slot_holds')
            ->where('order_id', $orderId)
            ->where('expires_at', '>', now())
            ->update([
                'expires_at' => now()->addMinutes(self::HOLD_MINUTES),
                'updated_at' => now(),
            ]);
    }


    public function releaseHoldsForOrder(int $orderId): void
    {
        if ($orderId < 1) {
            return;
        }

        DB::table('treatment_checkout_slot_holds')->where('order_id', $orderId)->delete();
    }


    public function purgeExpired(): int
    {
        return DB::table('treatment_checkout_slot_holds')
            ->where('expires_at', '<', now())
            ->delete();
    }


    public function hasConflict(
        int $beauticianId,
        string $date,
        string $startTime,
        int $durationMinutes,
        ?int $excludeOrderId = null,
    ): bool {
        $this->purgeExpired();

        $normalized = app(BeauticianAvailabilityService::class)->normalizeTime($startTime);

        if ($normalized === null) {
            return true;
        }

        $startMin = $this->minutes($normalized);
        $endMin = $startMin + max(1, $durationMinutes);

        $holds = DB::table('treatment_checkout_slot_holds')
            ->where('beautician_id', $beauticianId)
            ->where('appointment_date', $date)
            ->where('expires_at', '>', now())
            ->when($excludeOrderId, fn ($query) => $query->where('order_id', '!=', $excludeOrderId))
            ->get(['appointment_time', 'duration_minutes']);

        foreach ($holds as $hold) {
            $holdStart = $this->minutes((string) $hold->appointment_time);

            if ($holdStart === null) {
                continue;
            }

            $holdEnd = $holdStart + max(1, (int) $hold->duration_minutes);

            if ($startMin < $holdEnd && $endMin > $holdStart) {
                return true;
            }
        }

        return false;
    }


    private function minutes(string $time): ?int
    {
        $normalized = substr($time, 0, 5);

        if (! preg_match('/^\d{2}:\d{2}$/', $normalized)) {
            return null;
        }

        [$hour, $minute] = array_map('intval', explode(':', $normalized));

        return ($hour * 60) + $minute;
    }
}
