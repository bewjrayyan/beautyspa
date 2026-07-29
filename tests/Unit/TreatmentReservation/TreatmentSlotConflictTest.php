<?php

namespace Tests\Unit\TreatmentReservation;

use Modules\TreatmentReservation\Support\TreatmentSlotConflict;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TreatmentSlotConflictTest extends TestCase
{
    #[Test]
    public function it_recognizes_a_nested_database_slot_collision_without_matching_unrelated_errors(): void
    {
        $databaseError = new RuntimeException(
            "SQLSTATE[23000]: Duplicate entry for key 'treatment_slot_unique'"
        );
        $wrapped = new RuntimeException('Database operation failed.', 0, $databaseError);

        $this->assertTrue(TreatmentSlotConflict::causedBy($wrapped));
        $this->assertFalse(TreatmentSlotConflict::causedBy(new RuntimeException('Connection timed out.')));
    }
}
