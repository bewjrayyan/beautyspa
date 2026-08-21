<?php

namespace Tests\Feature\TreatmentReservation;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Beautician\Entities\Beautician;
use Modules\Product\Entities\Product;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\TreatmentReservation\Entities\AppointmentDateOverride;
use Modules\TreatmentReservation\Entities\BeauticianBlockedTime;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Http\Controllers\Admin\AppointmentAvailabilityController;
use Modules\TreatmentReservation\Http\Controllers\AvailabilitySlotsController;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityAdminService;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Scenarios 1–13 for the centralized AppointmentAvailabilityService.
 * Uses transactions so production data is never permanently changed.
 */
class AppointmentAvailabilityScenariosTest extends TestCase
{
    use DatabaseTransactions;

    private AppointmentAvailabilityAdminService $admin;

    private AppointmentAvailabilityService $engine;

    private SpaBranch $hq;

    private SpaBranch $jb;

    private int $productId;

    private int $dripProductId;

    protected function setUp(): void
    {
        parent::setUp();

        if (! app('modules')->isEnabled('SpaBranch') || ! app('modules')->isEnabled('TreatmentReservation')) {
            $this->markTestSkipped('Required modules not enabled.');
        }

        $this->admin = app(AppointmentAvailabilityAdminService::class);
        $this->engine = app(AppointmentAvailabilityService::class);

        $suffix = substr(str_replace('.', '', uniqid('', true)), -8);
        $this->hq = SpaBranch::query()->create([
            'name' => "TR Test HQ {$suffix}",
            'code' => "TRHQ{$suffix}",
            'phone' => '',
            'email' => '',
            'address' => 'Test HQ',
            'position' => 90,
            'is_active' => true,
        ]);
        $this->jb = SpaBranch::query()->create([
            'name' => "TR Test JB {$suffix}",
            'code' => "TRJB{$suffix}",
            'phone' => '',
            'email' => '',
            'address' => 'Test JB',
            'position' => 91,
            'is_active' => true,
        ]);

        $aura = Product::withoutGlobalScope('active')
            ->where('is_virtual', true)
            ->where('slug', 'like', '%aura-curve%')
            ->orderBy('id')
            ->first();
        $drip = Product::withoutGlobalScope('active')
            ->where('is_virtual', true)
            ->where('slug', 'drip')
            ->first();

        if (! $aura || ! $drip) {
            $this->markTestSkipped('Aura Curve / Drip products required for scenario tests.');
        }

        $this->productId = (int) $aura->id;
        $this->dripProductId = (int) $drip->id;

        $hqDays = $this->daysOpen([5, 6, 0], ['12:00', '14:00', '16:00', '18:00']);
        $jbDays = $this->outletDays();

        $this->admin->syncBranchWeekly((int) $this->hq->id, $hqDays);
        $this->admin->syncBranchWeekly((int) $this->jb->id, $jbDays);

        $this->admin->syncTreatmentBranch($this->productId, (int) $this->hq->id, [
            'duration_minutes' => 180,
            'capacity_per_slot' => 1,
            'allow_tba' => true,
            'is_bookable' => true,
            'days' => $hqDays,
        ]);
        $this->admin->syncTreatmentBranch($this->productId, (int) $this->jb->id, [
            'duration_minutes' => 180,
            'capacity_per_slot' => 1,
            'allow_tba' => true,
            'is_bookable' => true,
            'days' => $jbDays,
        ]);
        $this->admin->syncTreatmentBranch($this->dripProductId, (int) $this->hq->id, [
            'duration_minutes' => 60,
            'capacity_per_slot' => 5,
            'allow_tba' => true,
            'is_bookable' => true,
            'days' => $hqDays,
        ]);
    }

    #[Test]
    public function scenario_01_weekly_closed_day_has_no_slots(): void
    {
        $wednesday = Carbon::parse('next wednesday')->toDateString();
        $day = $this->engine->resolveDaySchedule($this->productId, (int) $this->hq->id, $wednesday);

        $this->assertFalse($day['open']);
        $this->assertSame([], $this->engine->availableSlots($this->productId, (int) $this->hq->id, $wednesday));
    }

    #[Test]
    public function scenario_02_special_open_override_unlocks_closed_weekday(): void
    {
        $wednesday = Carbon::parse('next wednesday')->toDateString();

        $this->admin->upsertDateOverride([
            'spa_branch_id' => (int) $this->hq->id,
            'product_id' => $this->productId,
            'override_date' => $wednesday,
            'status' => AppointmentDateOverride::STATUS_CUSTOM,
            'times' => ['14:00', '16:00'],
            'reason' => 'Special open',
        ]);

        $day = $this->engine->resolveDaySchedule($this->productId, (int) $this->hq->id, $wednesday);
        $this->assertTrue($day['open']);
        $this->assertContains('14:00', $this->engine->availableSlots($this->productId, (int) $this->hq->id, $wednesday));
    }

    #[Test]
    public function scenario_03_closed_override_blocks_normally_open_friday(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();

        $this->admin->upsertDateOverride([
            'spa_branch_id' => (int) $this->hq->id,
            'product_id' => $this->productId,
            'override_date' => $friday,
            'status' => AppointmentDateOverride::STATUS_CLOSED,
            'times' => [],
            'reason' => 'Closed Friday',
        ]);

        $this->assertFalse($this->engine->resolveDaySchedule($this->productId, (int) $this->hq->id, $friday)['open']);
        $this->assertSame([], $this->engine->availableSlots($this->productId, (int) $this->hq->id, $friday));
    }

    #[Test]
    public function scenario_04_admin_time_change_reflects_without_code_change(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();
        $before = $this->engine->availableSlots($this->productId, (int) $this->hq->id, $friday);
        $this->assertContains('12:00', $before);

        $newDays = $this->daysOpen([5, 6, 0], ['13:00', '15:00']);
        $this->admin->syncTreatmentBranch($this->productId, (int) $this->hq->id, [
            'duration_minutes' => 180,
            'capacity_per_slot' => 1,
            'allow_tba' => true,
            'is_bookable' => true,
            'days' => $newDays,
        ]);

        $after = $this->engine->availableSlots($this->productId, (int) $this->hq->id, $friday);
        $this->assertContains('13:00', $after);
        $this->assertNotContains('12:00', $after);
    }

    #[Test]
    public function scenario_05_branch_schedules_do_not_mix(): void
    {
        $tuesday = Carbon::parse('next tuesday')->toDateString();

        $hq = $this->engine->resolveDaySchedule($this->productId, (int) $this->hq->id, $tuesday);
        $jb = $this->engine->resolveDaySchedule($this->productId, (int) $this->jb->id, $tuesday);

        $this->assertFalse($hq['open'], 'HQ Aura closed Tue');
        $this->assertTrue($jb['open'], 'JB Aura open Tue');
        $this->assertContains('14:00', $jb['times']);
        $this->assertNotContains('18:00', $jb['times']);
    }

    #[Test]
    public function scenario_06_drip_capacity_five_rejects_sixth(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();
        $time = '14:00';

        for ($i = 1; $i <= 5; $i++) {
            $this->makeBooking($this->dripProductId, (int) $this->hq->id, $friday, $time);
        }

        $slots = $this->engine->availableSlots($this->dripProductId, (int) $this->hq->id, $friday);
        $this->assertNotContains($time, $slots);

        $this->expectException(\InvalidArgumentException::class);
        $this->engine->assertSlotBookable($this->dripProductId, (int) $this->hq->id, $friday, $time);
    }

    #[Test]
    public function scenario_07_capacity_change_to_seven_accepts_more_bookings(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();
        $time = '16:00';

        $this->admin->syncTreatmentBranch($this->dripProductId, (int) $this->hq->id, [
            'duration_minutes' => 60,
            'capacity_per_slot' => 7,
            'allow_tba' => true,
            'is_bookable' => true,
            'days' => $this->daysOpen([5, 6, 0], ['12:00', '14:00', '16:00', '18:00']),
        ]);

        for ($i = 1; $i <= 7; $i++) {
            $this->makeBooking($this->dripProductId, (int) $this->hq->id, $friday, $time);
        }

        $this->assertNotContains($time, $this->engine->availableSlots($this->dripProductId, (int) $this->hq->id, $friday));
        $this->assertTrue(
            $this->engine->isSlotAvailable($this->dripProductId, (int) $this->hq->id, $friday, '12:00')
        );
    }

    #[Test]
    public function scenario_08_tba_does_not_consume_capacity(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();
        $time = '12:00';

        TreatmentBooking::query()->create([
            'source' => TreatmentBooking::SOURCE_ADMIN_MANUAL,
            'product_id' => $this->productId,
            'spa_branch_id' => (int) $this->hq->id,
            'customer_first_name' => 'TBA',
            'customer_last_name' => 'Guest',
            'customer_phone' => '601100000008',
            'appointment_date' => null,
            'appointment_time' => null,
            'schedule_status' => TreatmentBooking::SCHEDULE_STATUS_TBA,
            'status' => TreatmentBooking::STATUS_PENDING,
            'total' => 0,
            'currency' => 'MYR',
            'payment_status' => TreatmentBooking::PAYMENT_DEPOSIT,
        ]);

        $this->assertContains($time, $this->engine->availableSlots($this->productId, (int) $this->hq->id, $friday));
    }

    #[Test]
    public function scenario_09_tba_can_be_scheduled_into_valid_slot(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();
        $time = '14:00';

        $this->assertTrue($this->engine->isSlotAvailable($this->productId, (int) $this->hq->id, $friday, $time));

        DB::transaction(function () use ($friday, $time) {
            $this->engine->assertSlotBookable($this->productId, (int) $this->hq->id, $friday, $time);
            $this->makeBooking($this->productId, (int) $this->hq->id, $friday, $time);
        });

        $this->assertFalse($this->engine->isSlotAvailable($this->productId, (int) $this->hq->id, $friday, $time));
    }

    #[Test]
    public function scenario_10_tba_schedule_into_full_slot_rejected(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();
        $time = '16:00';
        $this->makeBooking($this->productId, (int) $this->hq->id, $friday, $time);

        $this->expectException(\InvalidArgumentException::class);
        $this->engine->assertSlotBookable($this->productId, (int) $this->hq->id, $friday, $time);
    }

    #[Test]
    public function scenario_11_duration_overlap_detected_for_beautician(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();
        $beauticianId = $this->ensureBeauticianWithFridayHours();

        // Existing 12:00 booking lasting 180 minutes blocks 14:00 for same beautician.
        $this->makeBooking($this->productId, (int) $this->hq->id, $friday, '12:00', $beauticianId);

        $slots = $this->engine->availableSlots(
            $this->productId,
            (int) $this->hq->id,
            $friday,
            $beauticianId
        );

        $this->assertNotContains('12:00', $slots);
        $this->assertNotContains('14:00', $slots);
        $this->assertContains('16:00', $slots);
        $this->assertContains('18:00', $slots);
    }

    private function ensureBeauticianWithFridayHours(): int
    {
        $beautician = \Modules\Beautician\Entities\Beautician::query()->create([
            'first_name' => 'TR',
            'last_name' => 'Tester' . random_int(100, 999),
            'phone' => '6011' . random_int(10000000, 99999999),
            'is_active' => true,
            'position' => 99,
        ]);

        foreach (range(0, 6) as $dow) {
            \Modules\TreatmentReservation\Entities\BeauticianWorkingHour::query()->create([
                'beautician_id' => $beautician->id,
                'day_of_week' => $dow,
                'start_time' => '10:00:00',
                'end_time' => '22:00:00',
            ]);
        }

        return (int) $beautician->id;
    }

    #[Test]
    public function scenario_12_schedule_change_does_not_cancel_existing_booking(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();
        $booking = $this->makeBooking($this->productId, (int) $this->hq->id, $friday, '12:00');

        $this->admin->syncTreatmentBranch($this->productId, (int) $this->hq->id, [
            'duration_minutes' => 180,
            'capacity_per_slot' => 1,
            'allow_tba' => true,
            'is_bookable' => true,
            'days' => $this->daysOpen([5, 6, 0], ['15:00', '17:00']),
        ]);

        $fresh = $booking->fresh();
        $this->assertSame(TreatmentBooking::STATUS_PENDING, $fresh->status);
        $this->assertSame($friday, $fresh->appointment_date?->toDateString());
        $this->assertSame('12:00', substr((string) $fresh->getRawOriginal('appointment_time') ?: $fresh->appointment_time, 0, 5));
    }

    #[Test]
    public function scenario_13_concurrent_last_slot_does_not_oversell(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();
        $time = '18:00';

        DB::transaction(function () use ($friday, $time) {
            $this->engine->assertSlotBookable($this->productId, (int) $this->hq->id, $friday, $time);
            $this->makeBooking($this->productId, (int) $this->hq->id, $friday, $time);

            try {
                $this->engine->assertSlotBookable($this->productId, (int) $this->hq->id, $friday, $time);
                $this->fail('Expected second claim of last slot to be rejected.');
            } catch (\InvalidArgumentException $exception) {
                $this->assertNotSame('', $exception->getMessage());
            }
        });

        $this->assertDatabaseHas('appointment_availability_locks', [
            'lock_key' => "treatment:{$this->productId}:{$this->hq->id}:{$friday}",
        ]);
    }

    #[Test]
    public function public_slot_endpoint_rejects_partial_treatment_scope(): void
    {
        $beauticianId = $this->ensureBeauticianWithFridayHours();
        $request = Request::create('/availability/slots', 'GET', [
            'date' => Carbon::parse('next friday')->toDateString(),
            'product_id' => $this->productId,
        ]);

        try {
            app(AvailabilitySlotsController::class)($request, $beauticianId);
            $this->fail('Expected partial appointment scope to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('spa_branch_id', $exception->errors());
        }
    }

    #[Test]
    public function public_slot_endpoint_returns_only_scoped_appointment_slots(): void
    {
        $beauticianId = $this->ensureBeauticianWithFridayHours();
        Beautician::query()->findOrFail($beauticianId)->spaBranches()->sync([$this->hq->id]);

        $request = Request::create('/availability/slots', 'GET', [
            'date' => Carbon::parse('next friday')->toDateString(),
            'spa_branch_id' => $this->hq->id,
            'product_id' => $this->productId,
        ]);

        $response = app(AvailabilitySlotsController::class)($request, $beauticianId);

        $this->assertSame(200, $response->status());
        $this->assertSame(['12:00', '14:00', '16:00', '18:00'], $response->getData(true)['slots']);
    }

    #[Test]
    public function branch_custom_override_takes_precedence_over_treatment_weekly_rules(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();

        $this->admin->upsertDateOverride([
            'spa_branch_id' => (int) $this->hq->id,
            'product_id' => AppointmentDateOverride::PRODUCT_ALL,
            'override_date' => $friday,
            'status' => AppointmentDateOverride::STATUS_CUSTOM,
            'times' => ['15:00'],
            'reason' => 'Branch event',
        ]);

        $day = $this->engine->resolveDaySchedule($this->productId, (int) $this->hq->id, $friday);

        $this->assertSame('branch_date_override', $day['source']);
        $this->assertSame(['15:00'], $day['times']);
    }

    #[Test]
    public function partial_beautician_block_removes_every_overlapping_treatment_slot(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();
        $beauticianId = $this->ensureBeauticianWithFridayHours();

        BeauticianBlockedTime::query()->create([
            'beautician_id' => $beauticianId,
            'block_date' => $friday,
            'start_time' => '13:00',
            'end_time' => '15:00',
            'note' => 'Training',
        ]);

        $slots = $this->engine->availableSlots(
            $this->productId,
            (int) $this->hq->id,
            $friday,
            $beauticianId,
        );

        $this->assertNotContains('12:00', $slots, '12:00-15:00 overlaps the blocked window.');
        $this->assertNotContains('14:00', $slots, '14:00-17:00 overlaps the blocked window.');
        $this->assertContains('16:00', $slots);
    }

    #[Test]
    public function duration_snapshot_keeps_existing_booking_overlap_stable_after_setting_change(): void
    {
        $friday = Carbon::parse('next friday')->toDateString();
        $beauticianId = $this->ensureBeauticianWithFridayHours();
        $booking = $this->makeBooking($this->productId, (int) $this->hq->id, $friday, '12:00', $beauticianId);
        $booking->update(['duration_minutes_snapshot' => 180]);

        $this->admin->syncTreatmentBranch($this->productId, (int) $this->hq->id, [
            'duration_minutes' => 30,
            'capacity_per_slot' => 1,
            'allow_tba' => true,
            'is_bookable' => true,
            'days' => $this->daysOpen([5, 6, 0], ['12:00', '14:00', '16:00', '18:00']),
        ]);

        $slots = $this->engine->availableSlots(
            $this->productId,
            (int) $this->hq->id,
            $friday,
            $beauticianId,
        );

        $this->assertNotContains('14:00', $slots);
        $this->assertContains('16:00', $slots);
    }

    #[Test]
    public function branch_scope_only_lists_branch_wide_date_overrides(): void
    {
        $date = today()->addMonth()->toDateString();
        $branchOverride = $this->createOverride(AppointmentDateOverride::PRODUCT_ALL, $date, ['10:00']);
        $this->createOverride($this->productId, $date, ['14:00']);

        $view = app(AppointmentAvailabilityController::class)->index(Request::create(
            '/admin/treatment-reservations/appointment-availability',
            'GET',
            ['spa_branch_id' => (int) $this->hq->id]
        ));

        $overrides = $view->getData()['overrides'];

        $this->assertSame([$branchOverride->id], $overrides->pluck('id')->all());
        $this->assertTrue($overrides->every(fn (AppointmentDateOverride $override) => $override->isBranchWide()));
    }

    #[Test]
    public function treatment_scope_lists_inherited_branch_and_selected_treatment_overrides_only(): void
    {
        $date = today()->addMonth()->toDateString();
        $branchOverride = $this->createOverride(AppointmentDateOverride::PRODUCT_ALL, $date, ['10:00']);
        $selectedOverride = $this->createOverride($this->productId, $date, ['14:00']);
        $this->createOverride($this->dripProductId, $date, ['16:00']);

        $view = app(AppointmentAvailabilityController::class)->index(Request::create(
            '/admin/treatment-reservations/appointment-availability',
            'GET',
            [
                'spa_branch_id' => (int) $this->hq->id,
                'product_id' => $this->productId,
            ]
        ));

        $overrides = $view->getData()['overrides'];

        $this->assertEqualsCanonicalizing(
            [$branchOverride->id, $selectedOverride->id],
            $overrides->pluck('id')->all()
        );
        $this->assertSame(
            Product::withoutGlobalScope('active')->findOrFail($this->productId)->name,
            $overrides->firstWhere('id', $selectedOverride->id)->product->name
        );
    }

    private function createOverride(int $productId, string $date, array $times): AppointmentDateOverride
    {
        return $this->admin->upsertDateOverride([
            'spa_branch_id' => (int) $this->hq->id,
            'product_id' => $productId,
            'override_date' => $date,
            'status' => AppointmentDateOverride::STATUS_CUSTOM,
            'times' => $times,
            'reason' => 'Scope test',
        ]);
    }

    private function makeBooking(
        int $productId,
        int $spaBranchId,
        string $date,
        string $time,
        ?int $beauticianId = null,
    ): TreatmentBooking {
        return TreatmentBooking::query()->create([
            'source' => TreatmentBooking::SOURCE_ADMIN_MANUAL,
            'product_id' => $productId,
            'spa_branch_id' => $spaBranchId,
            'beautician_id' => $beauticianId,
            'customer_first_name' => 'Test',
            'customer_last_name' => 'Customer',
            'customer_phone' => '6011' . random_int(10000000, 99999999),
            'appointment_date' => $date,
            'appointment_time' => $time,
            'schedule_status' => null,
            'status' => TreatmentBooking::STATUS_PENDING,
            'total' => 0,
            'currency' => 'MYR',
            'payment_status' => TreatmentBooking::PAYMENT_DEPOSIT,
        ]);
    }

    private function daysOpen(array $openDays, array $times): array
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

    private function outletDays(): array
    {
        $days = [];
        foreach (range(0, 6) as $dow) {
            if ($dow === 1) {
                $days[] = ['day_of_week' => $dow, 'is_open' => false, 'times' => []];
                continue;
            }
            $weekend = in_array($dow, [5, 6, 0], true);
            $weekday = in_array($dow, [2, 3, 4], true);
            $times = $weekend ? ['12:00', '14:00', '16:00'] : ($weekday ? ['14:00', '16:00'] : []);
            $days[] = [
                'day_of_week' => $dow,
                'is_open' => $times !== [],
                'times' => $times,
            ];
        }

        return $days;
    }
}
