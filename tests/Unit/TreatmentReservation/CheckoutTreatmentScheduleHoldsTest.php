<?php

namespace Tests\Unit\TreatmentReservation;

use Modules\TreatmentReservation\Services\CheckoutTreatmentScheduleHolds;
use Tests\TestCase;

class CheckoutTreatmentScheduleHoldsTest extends TestCase
{
    public function test_same_beautician_cannot_start_another_treatment_during_held_duration(): void
    {
        $holds = [[
            'beautician_id' => 7,
            'appointment_date' => '2026-09-10',
            'appointment_time' => '10:00',
            'product_id' => 1,
            'duration_minutes' => 60,
        ]];

        $this->assertTrue(CheckoutTreatmentScheduleHolds::slotConflictsWithHolds(7, '2026-09-10', '10:00', 60, $holds));
        $this->assertTrue(CheckoutTreatmentScheduleHolds::slotConflictsWithHolds(7, '2026-09-10', '10:30', 60, $holds));
        $this->assertFalse(CheckoutTreatmentScheduleHolds::slotConflictsWithHolds(7, '2026-09-10', '11:00', 60, $holds));
        $this->assertFalse(CheckoutTreatmentScheduleHolds::slotConflictsWithHolds(8, '2026-09-10', '10:00', 60, $holds));
    }
}
