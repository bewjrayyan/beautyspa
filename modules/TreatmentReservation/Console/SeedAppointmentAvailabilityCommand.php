<?php

namespace Modules\TreatmentReservation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Modules\Product\Entities\Product;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityAdminService;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityService;

/**
 * Seeds DB-driven schedules by name/slug lookup (never hardcodes IDs).
 * Safe to re-run; overwrites weekly rows for matched branch/treatment pairs.
 *
 * Callers: artisan `treatment-reservation:seed-appointment-availability`
 * (registered in TreatmentReservationServiceProvider).
 */
class SeedAppointmentAvailabilityCommand extends Command
{
    protected $signature = 'treatment-reservation:seed-appointment-availability
                            {--dry-run : Show matches without writing}
                            {--create-missing-branches : Create SP/JB spa branches when missing}';

    protected $description = 'Seed initial treatment/branch appointment availability from business defaults.';


    public function handle(
        AppointmentAvailabilityAdminService $admin,
        AppointmentAvailabilityService $engine,
    ): int {
        $hq = $this->resolveHqBranch();

        if (! $hq) {
            $this->error('No HQ spa branch found.');

            return self::FAILURE;
        }

        $sp = $this->resolveNamedBranch(
            'SP',
            ['Sungai Petani', 'Kedah'],
            ['SP', 'SGPT'],
            [
                'name' => 'IMMA Sungai Petani',
                'code' => 'SP01',
                'phone' => '',
                'email' => '',
                'address' => 'Sungai Petani, Kedah',
                'position' => 2,
            ]
        );

        $jb = $this->resolveNamedBranch(
            'JB',
            ['Johor', 'Johor Bahru'],
            ['JB', 'JHB'],
            [
                'name' => 'IMMA Johor Bahru',
                'code' => 'JB01',
                'phone' => '',
                'email' => '',
                'address' => 'Johor Bahru, Johor',
                'position' => 3,
            ]
        );

        $hqTimes = ['12:00', '14:00', '16:00', '18:00'];
        $hqDays = $this->weeklyDays([5, 6, 0], $hqTimes);
        $outletDays = $this->outletWeeklyDays();

        $this->info("HQ branch: {$hq->name} (#{$hq->id})");

        if (! $this->option('dry-run')) {
            $admin->syncBranchWeekly((int) $hq->id, $hqDays);
        }

        foreach ([['branch' => $sp, 'label' => 'SP'], ['branch' => $jb, 'label' => 'JB']] as $row) {
            if (! $row['branch']) {
                $this->warn("{$row['label']} branch not found — skip (pass --create-missing-branches to create).");
                continue;
            }

            $this->info("{$row['label']} branch: {$row['branch']->name} (#{$row['branch']->id})");

            if (! $this->option('dry-run')) {
                $admin->syncBranchWeekly((int) $row['branch']->id, $outletDays);
            }
        }

        $profiles = $this->treatmentProfiles($hqDays, $outletDays);
        $outlets = array_values(array_filter([$sp, $jb]));

        foreach ($profiles as $profile) {
            $products = $this->matchProducts($profile['match']);

            if ($products->isEmpty()) {
                $this->warn("No product matched for profile [{$profile['label']}] — configure via admin UI.");
                continue;
            }

            foreach ($products as $product) {
                $this->seedProductOnBranch(
                    $admin,
                    $product,
                    $hq,
                    $profile['hq'],
                    $profile['label']
                );

                foreach ($outlets as $outlet) {
                    $this->seedProductOnBranch(
                        $admin,
                        $product,
                        $outlet,
                        $profile['outlet'] ?? $profile['hq'],
                        $profile['label']
                    );
                }
            }
        }

        if (! $this->option('dry-run')) {
            $this->runSmokeChecks($engine, $hq, $profiles);
        }

        $this->info('Done. Admin UI: Treatment Reservations → Appointment Availability');

        return self::SUCCESS;
    }


    private function resolveHqBranch(): ?SpaBranch
    {
        return SpaBranch::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('code', 'like', '%KJG%')
                    ->orWhere('name', 'like', '%HQ%')
                    ->orWhere('name', 'like', '%Kuala Lumpur%')
                    ->orWhere('name', 'like', '%Seri Laris%')
                    ->orWhere('name', 'like', '%Kajang%');
            })
            ->orderBy('position')
            ->first()
            ?? SpaBranch::query()->where('is_active', true)->orderBy('position')->first();
    }


    /**
     * @param  list<string>  $nameNeedles
     * @param  list<string>  $codeNeedles
     * @param  array{name: string, code: string, phone: string, email: string, address: string, position: int}  $create
     */
    private function resolveNamedBranch(
        string $label,
        array $nameNeedles,
        array $codeNeedles,
        array $create,
    ): ?SpaBranch {
        $existing = SpaBranch::query()
            ->where('is_active', true)
            ->where(function ($q) use ($nameNeedles, $codeNeedles) {
                foreach ($nameNeedles as $needle) {
                    $q->orWhere('name', 'like', '%' . $needle . '%');
                }
                foreach ($codeNeedles as $needle) {
                    $q->orWhere('code', 'like', '%' . $needle . '%');
                }
            })
            ->orderBy('position')
            ->first();

        if ($existing) {
            return $existing;
        }

        if (! $this->option('create-missing-branches')) {
            return null;
        }

        if ($this->option('dry-run')) {
            $this->line("[dry-run] Would create {$label} branch {$create['code']}");

            return null;
        }

        $branch = SpaBranch::query()->updateOrCreate(
            ['code' => $create['code']],
            array_merge($create, ['is_active' => true])
        );

        $this->info("Created {$label} branch: {$branch->name} (#{$branch->id})");

        return $branch;
    }


    private function seedProductOnBranch(
        AppointmentAvailabilityAdminService $admin,
        Product $product,
        SpaBranch $branch,
        array $payload,
        string $label,
    ): void {
        if ($this->option('dry-run')) {
            $this->line("[dry-run] {$label} → {$product->slug} @ {$branch->code} duration={$payload['duration_minutes']} capacity={$payload['capacity_per_slot']}");

            return;
        }

        $admin->syncTreatmentBranch((int) $product->id, (int) $branch->id, $payload);
        $this->info("{$label}: {$product->slug} @ {$branch->code} (duration={$payload['duration_minutes']}m, capacity={$payload['capacity_per_slot']})");
    }


    private function treatmentProfiles(array $hqDays, array $outletDays): array
    {
        $baseHq = [
            'allow_tba' => true,
            'is_bookable' => true,
            'days' => $hqDays,
        ];
        $baseOutlet = [
            'allow_tba' => true,
            'is_bookable' => true,
            'days' => $outletDays,
        ];

        return [
            [
                'label' => 'Aura Curve',
                'match' => [
                    'slug_contains' => ['aura-curve'],
                    'limit' => 3,
                ],
                'hq' => $baseHq + ['duration_minutes' => 180, 'capacity_per_slot' => 1],
                'outlet' => $baseOutlet + ['duration_minutes' => 180, 'capacity_per_slot' => 1],
            ],
            [
                'label' => 'Drip',
                'match' => [
                    'slugs' => ['drip'],
                    'limit' => 1,
                ],
                'hq' => $baseHq + ['duration_minutes' => 60, 'capacity_per_slot' => 5],
                'outlet' => $baseOutlet + ['duration_minutes' => 60, 'capacity_per_slot' => 5],
            ],
            [
                'label' => 'Detox',
                'match' => [
                    'slug_contains' => ['detox-booster', 'detox'],
                    'name_contains' => ['Detox Booster', 'DETOX PREMIUM'],
                    'limit' => 8,
                ],
                'hq' => $baseHq + ['duration_minutes' => 60, 'capacity_per_slot' => 1],
                'outlet' => $baseOutlet + ['duration_minutes' => 60, 'capacity_per_slot' => 1],
            ],
            [
                'label' => 'Highway Premium',
                'match' => [
                    'slug_contains' => ['highway-premium', 'highway'],
                    'name_contains' => ['HIGHWAY PREMIUM', 'Highway Premium'],
                    'limit' => 8,
                ],
                'hq' => $baseHq + ['duration_minutes' => 60, 'capacity_per_slot' => 1],
                'outlet' => $baseOutlet + ['duration_minutes' => 60, 'capacity_per_slot' => 1],
            ],
            [
                'label' => 'Anak Dara',
                'match' => [
                    'slug_contains' => ['anak-dara'],
                    'name_contains' => ['ANAK DARA', 'Anak Dara'],
                    'limit' => 8,
                ],
                'hq' => $baseHq + ['duration_minutes' => 60, 'capacity_per_slot' => 1],
                'outlet' => $baseOutlet + ['duration_minutes' => 60, 'capacity_per_slot' => 1],
            ],
        ];
    }


    private function matchProducts(array $match): Collection
    {
        $query = Product::withoutGlobalScope('active')->where('is_virtual', true);

        $query->where(function ($outer) use ($match) {
            foreach ($match['slugs'] ?? [] as $slug) {
                $outer->orWhere('slug', $slug);
            }

            foreach ($match['slug_contains'] ?? [] as $needle) {
                $outer->orWhere('slug', 'like', '%' . $needle . '%');
            }

            foreach ($match['name_contains'] ?? [] as $needle) {
                $outer->orWhereHas('translations', function ($t) use ($needle) {
                    $t->where('name', 'like', '%' . $needle . '%');
                });
            }
        });

        $limit = max(1, (int) ($match['limit'] ?? 5));

        return $query
            ->with('translations')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->unique('id')
            ->values();
    }


    private function outletWeeklyDays(): array
    {
        $weekdayTimes = ['14:00', '16:00'];
        $weekendTimes = ['12:00', '14:00', '16:00'];
        $days = [];

        foreach (range(0, 6) as $dow) {
            if ($dow === 1) {
                $days[] = ['day_of_week' => $dow, 'is_open' => false, 'times' => []];
                continue;
            }

            $weekend = in_array($dow, [5, 6, 0], true);
            $weekday = in_array($dow, [2, 3, 4], true);
            $times = $weekend ? $weekendTimes : ($weekday ? $weekdayTimes : []);

            $days[] = [
                'day_of_week' => $dow,
                'is_open' => $times !== [],
                'times' => $times,
            ];
        }

        return $days;
    }


    private function weeklyDays(array $openDays, array $times): array
    {
        $days = [];

        foreach (range(0, 6) as $dow) {
            $open = in_array($dow, $openDays, true);
            $days[] = [
                'day_of_week' => $dow,
                'is_open' => $open,
                'times' => $open ? $times : [],
            ];
        }

        return $days;
    }


    private function runSmokeChecks(
        AppointmentAvailabilityService $engine,
        SpaBranch $hq,
        array $profiles,
    ): void {
        $aura = $this->matchProducts($profiles[0]['match'])->first();

        if (! $aura) {
            return;
        }

        $friday = now()->next('Friday')->toDateString();
        $wed = now()->next('Wednesday')->toDateString();
        $fri = $engine->resolveDaySchedule((int) $aura->id, (int) $hq->id, $friday);
        $wedDay = $engine->resolveDaySchedule((int) $aura->id, (int) $hq->id, $wed);
        $this->line('Smoke Friday open=' . ($fri['open'] ? 'yes' : 'no') . ' times=' . implode(',', $fri['times']));
        $this->line('Smoke Wednesday open=' . ($wedDay['open'] ? 'yes' : 'no'));
    }
}
