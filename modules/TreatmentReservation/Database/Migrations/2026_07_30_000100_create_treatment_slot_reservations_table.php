<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ACTIVE_ORDER_STATUSES = "'pending','processing','completed'";

    private const ACTIVE_BOOKING_STATUSES = "'pending','in_progress'";

    public function up(): void
    {
        if (Schema::hasTable('treatment_slot_reservations')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            $this->assertExistingSlotsAreUnique();
        }

        Schema::create('treatment_slot_reservations', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedInteger('beautician_id');
            $table->date('appointment_date');
            $table->char('appointment_time', 5);
            $table->unsignedInteger('order_id')->nullable()->unique('treatment_slot_order_unique');
            $table->unsignedInteger('treatment_booking_id')->nullable()->unique('treatment_slot_booking_unique');
            $table->timestamps();

            $table->unique(
                ['beautician_id', 'appointment_date', 'appointment_time'],
                'treatment_slot_unique'
            );
        });

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        try {
            $this->backfillReservations();
            $this->createTriggers();
        } catch (\Throwable $exception) {
            $this->dropTriggers();
            Schema::dropIfExists('treatment_slot_reservations');

            throw $exception;
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $this->dropTriggers();
        }

        Schema::dropIfExists('treatment_slot_reservations');
    }

    private function assertExistingSlotsAreUnique(): void
    {
        $activeOrders = self::ACTIVE_ORDER_STATUSES;
        $activeBookings = self::ACTIVE_BOOKING_STATUSES;
        $orderTime = $this->normalizedTime('o.appointment_time');
        $bookingTime = $this->normalizedTime('tb.appointment_time');

        $invalid = DB::selectOne(<<<SQL
            SELECT COUNT(*) AS aggregate
            FROM (
                SELECT {$orderTime} AS slot_time
                FROM orders o
                WHERE o.deleted_at IS NULL
                  AND o.status IN ({$activeOrders})
                  AND o.beautician_id IS NOT NULL
                  AND o.appointment_date IS NOT NULL
                  AND o.appointment_time IS NOT NULL
                UNION ALL
                SELECT {$bookingTime} AS slot_time
                FROM treatment_bookings tb
                WHERE tb.deleted_at IS NULL
                  AND tb.status IN ({$activeBookings})
                  AND tb.beautician_id IS NOT NULL
                  AND tb.appointment_date IS NOT NULL
                  AND tb.appointment_time IS NOT NULL
                  AND NOT EXISTS (
                      SELECT 1
                      FROM orders linked_order
                      WHERE linked_order.id = tb.order_id
                        AND linked_order.deleted_at IS NULL
                        AND linked_order.status IN ({$activeOrders})
                        AND linked_order.beautician_id IS NOT NULL
                        AND linked_order.appointment_date IS NOT NULL
                        AND linked_order.appointment_time IS NOT NULL
                  )
            ) slots
            WHERE slot_time IS NULL
        SQL);

        if ((int) ($invalid->aggregate ?? 0) > 0) {
            throw new RuntimeException(
                'Cannot enforce treatment slot uniqueness: active appointments contain an invalid time value.'
            );
        }

        $duplicate = DB::selectOne(<<<SQL
            SELECT beautician_id, appointment_date, appointment_time, COUNT(*) AS aggregate
            FROM (
                SELECT o.beautician_id, o.appointment_date, {$orderTime} AS appointment_time
                FROM orders o
                WHERE o.deleted_at IS NULL
                  AND o.status IN ({$activeOrders})
                  AND o.beautician_id IS NOT NULL
                  AND o.appointment_date IS NOT NULL
                  AND o.appointment_time IS NOT NULL
                UNION ALL
                SELECT tb.beautician_id, tb.appointment_date, {$bookingTime} AS appointment_time
                FROM treatment_bookings tb
                WHERE tb.deleted_at IS NULL
                  AND tb.status IN ({$activeBookings})
                  AND tb.beautician_id IS NOT NULL
                  AND tb.appointment_date IS NOT NULL
                  AND tb.appointment_time IS NOT NULL
                  AND NOT EXISTS (
                      SELECT 1
                      FROM orders linked_order
                      WHERE linked_order.id = tb.order_id
                        AND linked_order.deleted_at IS NULL
                        AND linked_order.status IN ({$activeOrders})
                        AND linked_order.beautician_id IS NOT NULL
                        AND linked_order.appointment_date IS NOT NULL
                        AND linked_order.appointment_time IS NOT NULL
                  )
            ) slots
            GROUP BY beautician_id, appointment_date, appointment_time
            HAVING COUNT(*) > 1
            LIMIT 1
        SQL);

        if ($duplicate !== null) {
            throw new RuntimeException(
                'Cannot enforce treatment slot uniqueness: duplicate active appointment slots require review.'
            );
        }
    }

    private function backfillReservations(): void
    {
        $activeOrders = self::ACTIVE_ORDER_STATUSES;
        $activeBookings = self::ACTIVE_BOOKING_STATUSES;
        $orderTime = $this->normalizedTime('o.appointment_time');
        $bookingTime = $this->normalizedTime('tb.appointment_time');

        DB::statement(<<<SQL
            INSERT INTO treatment_slot_reservations
                (beautician_id, appointment_date, appointment_time, order_id, created_at, updated_at)
            SELECT o.beautician_id, o.appointment_date, {$orderTime}, o.id, NOW(), NOW()
            FROM orders o
            WHERE o.deleted_at IS NULL
              AND o.status IN ({$activeOrders})
              AND o.beautician_id IS NOT NULL
              AND o.appointment_date IS NOT NULL
              AND o.appointment_time IS NOT NULL
        SQL);

        DB::statement(<<<SQL
            UPDATE treatment_slot_reservations slot
            INNER JOIN treatment_bookings tb ON tb.order_id = slot.order_id
            SET slot.treatment_booking_id = tb.id,
                slot.updated_at = NOW()
            WHERE tb.deleted_at IS NULL
              AND tb.status IN ({$activeBookings})
        SQL);

        DB::statement(<<<SQL
            INSERT INTO treatment_slot_reservations
                (beautician_id, appointment_date, appointment_time, treatment_booking_id, created_at, updated_at)
            SELECT tb.beautician_id, tb.appointment_date, {$bookingTime}, tb.id, NOW(), NOW()
            FROM treatment_bookings tb
            WHERE tb.deleted_at IS NULL
              AND tb.status IN ({$activeBookings})
              AND tb.beautician_id IS NOT NULL
              AND tb.appointment_date IS NOT NULL
              AND tb.appointment_time IS NOT NULL
              AND NOT EXISTS (
                  SELECT 1 FROM treatment_slot_reservations slot WHERE slot.treatment_booking_id = tb.id
              )
        SQL);
    }

    private function createTriggers(): void
    {
        $activeOrders = self::ACTIVE_ORDER_STATUSES;
        $activeBookings = self::ACTIVE_BOOKING_STATUSES;
        $orderTime = $this->normalizedTime('NEW.appointment_time');
        $bookingTime = $this->normalizedTime('NEW.appointment_time');

        DB::unprepared(<<<SQL
            CREATE TRIGGER treatment_slot_orders_ai AFTER INSERT ON orders FOR EACH ROW
            BEGIN
                IF NEW.deleted_at IS NULL
                   AND NEW.status IN ({$activeOrders})
                   AND NEW.beautician_id IS NOT NULL
                   AND NEW.appointment_date IS NOT NULL
                   AND NEW.appointment_time IS NOT NULL THEN
                    INSERT INTO treatment_slot_reservations
                        (beautician_id, appointment_date, appointment_time, order_id, created_at, updated_at)
                    VALUES
                        (NEW.beautician_id, NEW.appointment_date, {$orderTime}, NEW.id, NOW(), NOW());
                END IF;
            END
        SQL);

        DB::unprepared(<<<SQL
            CREATE TRIGGER treatment_slot_orders_au AFTER UPDATE ON orders FOR EACH ROW
            BEGIN
                IF NEW.deleted_at IS NULL
                   AND NEW.status IN ({$activeOrders})
                   AND NEW.beautician_id IS NOT NULL
                   AND NEW.appointment_date IS NOT NULL
                   AND NEW.appointment_time IS NOT NULL THEN
                    UPDATE treatment_slot_reservations
                    SET beautician_id = NEW.beautician_id,
                        appointment_date = NEW.appointment_date,
                        appointment_time = {$orderTime},
                        updated_at = NOW()
                    WHERE order_id = NEW.id;

                    IF NOT EXISTS (
                        SELECT 1 FROM treatment_slot_reservations WHERE order_id = NEW.id
                    ) THEN
                        UPDATE treatment_slot_reservations slot
                        INNER JOIN treatment_bookings tb ON tb.id = slot.treatment_booking_id
                        SET slot.order_id = NEW.id,
                            slot.beautician_id = NEW.beautician_id,
                            slot.appointment_date = NEW.appointment_date,
                            slot.appointment_time = {$orderTime},
                            slot.updated_at = NOW()
                        WHERE tb.order_id = NEW.id;
                    END IF;

                    IF NOT EXISTS (
                        SELECT 1 FROM treatment_slot_reservations WHERE order_id = NEW.id
                    ) THEN
                        INSERT INTO treatment_slot_reservations
                            (beautician_id, appointment_date, appointment_time, order_id, created_at, updated_at)
                        VALUES
                            (NEW.beautician_id, NEW.appointment_date, {$orderTime}, NEW.id, NOW(), NOW());
                    END IF;
                ELSE
                    UPDATE treatment_slot_reservations slot
                    INNER JOIN treatment_bookings tb ON tb.id = slot.treatment_booking_id
                    SET slot.order_id = NULL,
                        slot.updated_at = NOW()
                    WHERE slot.order_id = NEW.id
                      AND tb.deleted_at IS NULL
                      AND tb.status IN ({$activeBookings});

                    DELETE FROM treatment_slot_reservations WHERE order_id = NEW.id;
                END IF;
            END
        SQL);

        DB::unprepared(<<<SQL
            CREATE TRIGGER treatment_slot_orders_ad AFTER DELETE ON orders FOR EACH ROW
            BEGIN
                UPDATE treatment_slot_reservations slot
                INNER JOIN treatment_bookings tb ON tb.id = slot.treatment_booking_id
                SET slot.order_id = NULL,
                    slot.updated_at = NOW()
                WHERE slot.order_id = OLD.id
                  AND tb.deleted_at IS NULL
                  AND tb.status IN ({$activeBookings});

                DELETE FROM treatment_slot_reservations WHERE order_id = OLD.id;
            END
        SQL);

        DB::unprepared(<<<SQL
            CREATE TRIGGER treatment_slot_bookings_ai AFTER INSERT ON treatment_bookings FOR EACH ROW
            BEGIN
                IF NEW.deleted_at IS NULL
                   AND NEW.status IN ({$activeBookings})
                   AND NEW.beautician_id IS NOT NULL
                   AND NEW.appointment_date IS NOT NULL
                   AND NEW.appointment_time IS NOT NULL THEN
                    IF NEW.order_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM treatment_slot_reservations WHERE order_id = NEW.order_id
                    ) THEN
                        UPDATE treatment_slot_reservations
                        SET treatment_booking_id = NEW.id,
                            updated_at = NOW()
                        WHERE order_id = NEW.order_id;
                    ELSE
                        INSERT INTO treatment_slot_reservations
                            (beautician_id, appointment_date, appointment_time, treatment_booking_id, created_at, updated_at)
                        VALUES
                            (NEW.beautician_id, NEW.appointment_date, {$bookingTime}, NEW.id, NOW(), NOW());
                    END IF;
                END IF;
            END
        SQL);

        DB::unprepared(<<<SQL
            CREATE TRIGGER treatment_slot_bookings_au AFTER UPDATE ON treatment_bookings FOR EACH ROW
            BEGIN
                IF NEW.deleted_at IS NULL
                   AND NEW.status IN ({$activeBookings})
                   AND NEW.beautician_id IS NOT NULL
                   AND NEW.appointment_date IS NOT NULL
                   AND NEW.appointment_time IS NOT NULL THEN
                    IF NEW.order_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM treatment_slot_reservations WHERE order_id = NEW.order_id
                    ) THEN
                        DELETE FROM treatment_slot_reservations
                        WHERE treatment_booking_id = NEW.id
                          AND (order_id IS NULL OR order_id <> NEW.order_id);

                        UPDATE treatment_slot_reservations
                        SET treatment_booking_id = NEW.id,
                            updated_at = NOW()
                        WHERE order_id = NEW.order_id;
                    ELSE
                        UPDATE treatment_slot_reservations
                        SET order_id = NULL,
                            beautician_id = NEW.beautician_id,
                            appointment_date = NEW.appointment_date,
                            appointment_time = {$bookingTime},
                            updated_at = NOW()
                        WHERE treatment_booking_id = NEW.id;

                        IF NOT EXISTS (
                            SELECT 1
                            FROM treatment_slot_reservations
                            WHERE treatment_booking_id = NEW.id
                        ) THEN
                            INSERT INTO treatment_slot_reservations
                                (beautician_id, appointment_date, appointment_time, treatment_booking_id, created_at, updated_at)
                            VALUES
                                (NEW.beautician_id, NEW.appointment_date, {$bookingTime}, NEW.id, NOW(), NOW());
                        END IF;
                    END IF;
                ELSE
                    UPDATE treatment_slot_reservations slot
                    INNER JOIN orders linked_order ON linked_order.id = slot.order_id
                    SET slot.treatment_booking_id = NULL,
                        slot.updated_at = NOW()
                    WHERE slot.treatment_booking_id = NEW.id
                      AND linked_order.deleted_at IS NULL
                      AND linked_order.status IN ({$activeOrders});

                    DELETE FROM treatment_slot_reservations WHERE treatment_booking_id = NEW.id;
                END IF;
            END
        SQL);

        DB::unprepared(<<<SQL
            CREATE TRIGGER treatment_slot_bookings_ad AFTER DELETE ON treatment_bookings FOR EACH ROW
            BEGIN
                UPDATE treatment_slot_reservations slot
                INNER JOIN orders linked_order ON linked_order.id = slot.order_id
                SET slot.treatment_booking_id = NULL,
                    slot.updated_at = NOW()
                WHERE slot.treatment_booking_id = OLD.id
                  AND linked_order.deleted_at IS NULL
                  AND linked_order.status IN ({$activeOrders});

                DELETE FROM treatment_slot_reservations WHERE treatment_booking_id = OLD.id;
            END
        SQL);
    }

    private function dropTriggers(): void
    {
        foreach ([
            'treatment_slot_orders_ai',
            'treatment_slot_orders_au',
            'treatment_slot_orders_ad',
            'treatment_slot_bookings_ai',
            'treatment_slot_bookings_au',
            'treatment_slot_bookings_ad',
        ] as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }

    private function normalizedTime(string $column): string
    {
        return "COALESCE("
            . "DATE_FORMAT(STR_TO_DATE(TRIM({$column}), '%h:%i %p'), '%H:%i'),"
            . "DATE_FORMAT(STR_TO_DATE(TRIM({$column}), '%H:%i:%s'), '%H:%i'),"
            . "DATE_FORMAT(STR_TO_DATE(TRIM({$column}), '%H:%i'), '%H:%i')"
            . ')';
    }
};
