<?php

namespace Tests\Unit\TreatmentReservation;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MultiTreatmentBookingSchemaTest extends TestCase
{
    public function test_treatment_bookings_allow_multiple_rows_per_order_via_order_product_id(): void
    {
        $this->assertTrue(Schema::hasColumn('treatment_bookings', 'order_product_id'));

        $indexes = collect(Schema::getIndexes('treatment_bookings'));
        $orderIdUnique = $indexes->first(function (array $index) {
            return ($index['unique'] ?? false)
                && ($index['columns'] ?? []) === ['order_id'];
        });

        $this->assertNull($orderIdUnique, 'order_id must not be unique so one order can have N bookings');

        $orderProductUnique = $indexes->first(function (array $index) {
            return ($index['unique'] ?? false)
                && in_array('order_product_id', $index['columns'] ?? [], true);
        });

        $this->assertNotNull($orderProductUnique);
    }

    public function test_slot_reservations_do_not_unique_order_id(): void
    {
        if (! Schema::hasTable('treatment_slot_reservations')) {
            $this->markTestSkipped('slot reservations table missing');
        }

        $indexes = collect(Schema::getIndexes('treatment_slot_reservations'));
        $orderIdUnique = $indexes->first(function (array $index) {
            return ($index['unique'] ?? false)
                && ($index['columns'] ?? []) === ['order_id'];
        });

        $this->assertNull($orderIdUnique);
    }
}
