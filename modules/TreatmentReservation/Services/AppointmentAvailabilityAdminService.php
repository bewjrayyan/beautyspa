<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\DB;
use Modules\TreatmentReservation\Entities\AppointmentDateOverride;
use Modules\TreatmentReservation\Entities\AppointmentDateOverrideSlot;
use Modules\TreatmentReservation\Entities\SpaBranchWeeklyAvailability;
use Modules\TreatmentReservation\Entities\SpaBranchWeeklyAvailabilitySlot;
use Modules\TreatmentReservation\Entities\TreatmentBranchAvailability;
use Modules\TreatmentReservation\Entities\TreatmentBranchWeeklyAvailability;
use Modules\TreatmentReservation\Entities\TreatmentBranchWeeklyAvailabilitySlot;

/**
 * Persist admin schedule UI payloads into availability tables.
 */
class AppointmentAvailabilityAdminService
{
    /**
     * @param  list<array{day_of_week: int, is_open: bool, times?: list<string>}>  $days
     */
    public function syncBranchWeekly(int $spaBranchId, array $days): void
    {
        DB::transaction(function () use ($spaBranchId, $days) {
            SpaBranchWeeklyAvailability::query()
                ->where('spa_branch_id', $spaBranchId)
                ->each(function (SpaBranchWeeklyAvailability $row) {
                    $row->slots()->delete();
                    $row->delete();
                });

            foreach ($days as $day) {
                $row = SpaBranchWeeklyAvailability::create([
                    'spa_branch_id' => $spaBranchId,
                    'day_of_week' => (int) $day['day_of_week'],
                    'is_open' => (bool) ($day['is_open'] ?? false),
                ]);

                if (! $row->is_open) {
                    continue;
                }

                foreach ($this->normalizeTimes($day['times'] ?? []) as $time) {
                    SpaBranchWeeklyAvailabilitySlot::create([
                        'spa_branch_weekly_availability_id' => $row->id,
                        'start_time' => $time,
                        'is_enabled' => true,
                    ]);
                }
            }
        });
    }


    /**
     * @param  array{
     *     duration_minutes?: int|null,
     *     capacity_per_slot?: int,
     *     allow_tba?: bool,
     *     is_bookable?: bool,
     *     days: list<array{day_of_week: int, is_open: bool, times?: list<string>}>
     * }  $payload
     */
    public function syncTreatmentBranch(int $productId, int $spaBranchId, array $payload): TreatmentBranchAvailability
    {
        return DB::transaction(function () use ($productId, $spaBranchId, $payload) {
            $settings = TreatmentBranchAvailability::query()->updateOrCreate(
                [
                    'product_id' => $productId,
                    'spa_branch_id' => $spaBranchId,
                ],
                [
                    'duration_minutes' => isset($payload['duration_minutes']) && $payload['duration_minutes'] !== ''
                        ? (int) $payload['duration_minutes']
                        : null,
                    'capacity_per_slot' => max(1, (int) ($payload['capacity_per_slot'] ?? 1)),
                    'allow_tba' => (bool) ($payload['allow_tba'] ?? true),
                    'is_bookable' => (bool) ($payload['is_bookable'] ?? true),
                ]
            );

            $settings->weeklyDays()->each(function (TreatmentBranchWeeklyAvailability $day) {
                $day->slots()->delete();
                $day->delete();
            });

            foreach ($payload['days'] ?? [] as $day) {
                $row = TreatmentBranchWeeklyAvailability::create([
                    'treatment_branch_availability_id' => $settings->id,
                    'day_of_week' => (int) $day['day_of_week'],
                    'is_open' => (bool) ($day['is_open'] ?? false),
                ]);

                if (! $row->is_open) {
                    continue;
                }

                foreach ($this->normalizeTimes($day['times'] ?? []) as $time) {
                    TreatmentBranchWeeklyAvailabilitySlot::create([
                        'treatment_branch_weekly_availability_id' => $row->id,
                        'start_time' => $time,
                        'is_enabled' => true,
                    ]);
                }
            }

            return $settings->fresh(['weeklyDays.slots']);
        });
    }


    /**
     * @param  array{
     *     spa_branch_id: int,
     *     product_id?: int,
     *     override_date: string,
     *     status: string,
     *     reason?: string|null,
     *     capacity_per_slot?: int|null,
     *     duration_minutes?: int|null,
     *     times?: list<string>
     * }  $payload
     */
    public function upsertDateOverride(array $payload): AppointmentDateOverride
    {
        return DB::transaction(function () use ($payload) {
            $productId = (int) ($payload['product_id'] ?? AppointmentDateOverride::PRODUCT_ALL);
            $status = (string) $payload['status'];

            if (! in_array($status, AppointmentDateOverride::statuses(), true)) {
                throw new \InvalidArgumentException(
                    trans('treatmentreservation::admin.appointment_availability.invalid_status')
                );
            }

            $override = AppointmentDateOverride::query()->updateOrCreate(
                [
                    'spa_branch_id' => (int) $payload['spa_branch_id'],
                    'product_id' => $productId,
                    'override_date' => $payload['override_date'],
                ],
                [
                    'status' => $status,
                    'reason' => $payload['reason'] ?? null,
                    'capacity_per_slot' => isset($payload['capacity_per_slot']) && $payload['capacity_per_slot'] !== ''
                        ? max(1, (int) $payload['capacity_per_slot'])
                        : null,
                    'duration_minutes' => isset($payload['duration_minutes']) && $payload['duration_minutes'] !== ''
                        ? max(1, (int) $payload['duration_minutes'])
                        : null,
                ]
            );

            $override->slots()->delete();

            if ($status !== AppointmentDateOverride::STATUS_CLOSED) {
                foreach ($this->normalizeTimes($payload['times'] ?? []) as $time) {
                    AppointmentDateOverrideSlot::create([
                        'appointment_date_override_id' => $override->id,
                        'start_time' => $time,
                        'is_enabled' => true,
                    ]);
                }
            }

            return $override->fresh(['slots']);
        });
    }


    public function deleteDateOverride(int $overrideId): void
    {
        $override = AppointmentDateOverride::query()->findOrFail($overrideId);
        $override->slots()->delete();
        $override->delete();
    }


    /**
     * @param  list<string>  $times
     * @return list<string>
     */
    private function normalizeTimes(array $times): array
    {
        $availability = app(BeauticianAvailabilityService::class);
        $normalized = [];

        foreach ($times as $time) {
            $value = $availability->normalizeTime((string) $time);

            if ($value !== null) {
                $normalized[] = $value . ':00';
            }
        }

        return array_values(array_unique($normalized));
    }
}
