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

    public function test_pos_requests_are_idempotent_per_line(): void
    {
        $this->assertTrue(Schema::hasColumn("treatment_bookings", "pos_request_key"));
        $this->assertTrue(Schema::hasColumn("treatment_bookings", "pos_line_index"));
        $this->assertTrue(Schema::hasColumn("treatment_bookings", "pos_payload_hash"));

        $index = collect(Schema::getIndexes("treatment_bookings"))->first(
            fn (array $index) => ($index["unique"] ?? false)
                && ($index["columns"] ?? []) === ["pos_request_key", "pos_line_index"]
        );

        $this->assertNotNull($index, "POS request key and line index must be unique.");
    }

    public function test_slot_reservations_do_not_unique_order_id(): void
    {
        $this->assertTrue(Schema::hasTable('treatment_slot_reservations'), 'slot reservations table is required');

        $indexes = collect(Schema::getIndexes('treatment_slot_reservations'));
        $orderIdUnique = $indexes->first(function (array $index) {
            return ($index['unique'] ?? false)
                && ($index['columns'] ?? []) === ['order_id'];
        });

        $this->assertNull($orderIdUnique);
    }
}
