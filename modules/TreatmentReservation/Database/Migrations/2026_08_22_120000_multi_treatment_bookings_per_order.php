<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Allow 1 order → N treatment_bookings (one per order_product line).
 * Slot reservations become booking-driven; order appointment fields remain a snapshot only.
 */
return new class extends Migration
{
    private const ACTIVE_BOOKING_STATUSES = "'pending','in_progress'";

    public function up(): void
    {
        if (Schema::hasTable('treatment_bookings')) {
            $this->dropOrderIdUniqueOnBookings();
            $this->addOrderProductIdToBookings();
        }

        if (Schema::hasTable('treatment_slot_reservations')) {
            $this->dropOrderIdUniqueOnSlots();
            $this->rewriteSlotTriggers();
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('treatment_slot_reservations') && DB::connection()->getDriverName() === 'mysql') {
            $this->dropSlotTriggers();
        }

        if (Schema::hasTable('treatment_bookings') && Schema::hasColumn('treatment_bookings', 'order_product_id')) {
            Schema::table('treatment_bookings', function (Blueprint $table): void {
                $table->dropForeign('tb_order_product_fk');
                $table->dropUnique('tb_order_product_unique');
                $table->dropColumn('order_product_id');
            });
        }
    }

    private function dropOrderIdUniqueOnBookings(): void
    {
        $indexName = $this->findUniqueIndexName('treatment_bookings', 'order_id');

        if (! $indexName) {
            return;
        }

        // MySQL: unique index is also used by FK tb_order_fk — drop FK, swap index, restore FK.
        if (DB::connection()->getDriverName() === 'mysql') {
            $this->dropForeignIfExists('treatment_bookings', 'tb_order_fk');

            DB::statement("ALTER TABLE treatment_bookings DROP INDEX `{$indexName}`");

            if (! $this->hasIndex('treatment_bookings', 'treatment_bookings_order_id_index')) {
                Schema::table('treatment_bookings', function (Blueprint $table): void {
                    $table->index('order_id', 'treatment_bookings_order_id_index');
                });
            }

            Schema::table('treatment_bookings', function (Blueprint $table): void {
                $table->foreign('order_id', 'tb_order_fk')
                    ->references('id')->on('orders')->nullOnDelete();
            });

            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table) use ($indexName): void {
            $table->dropUnique($indexName);
            $table->index('order_id', 'treatment_bookings_order_id_index');
        });
    }

    private function dropForeignIfExists(string $table, string $foreignName): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME AS name
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = ?
             LIMIT 1',
            [$database, $table, $foreignName, 'FOREIGN KEY']
        );

        if (! $row) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($foreignName): void {
            $blueprint->dropForeign($foreignName);
        });
    }

    private function addOrderProductIdToBookings(): void
    {
        if (Schema::hasColumn('treatment_bookings', 'order_product_id')) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table): void {
            $table->unsignedInteger('order_product_id')->nullable()->after('order_id');
            $table->unique('order_product_id', 'tb_order_product_unique');
            $table->foreign('order_product_id', 'tb_order_product_fk')
                ->references('id')
                ->on('order_products')
                ->nullOnDelete();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement(<<<'SQL'
                UPDATE treatment_bookings tb
                INNER JOIN (
                    SELECT op.id AS order_product_id, op.order_id, op.product_id,
                           ROW_NUMBER() OVER (
                               PARTITION BY op.order_id, op.product_id
                               ORDER BY op.id
                           ) AS rn
                    FROM order_products op
                    INNER JOIN products p ON p.id = op.product_id AND p.is_virtual = 1
                ) matched ON matched.order_id = tb.order_id
                    AND matched.product_id = tb.product_id
                    AND matched.rn = 1
                SET tb.order_product_id = matched.order_product_id
                WHERE tb.order_id IS NOT NULL
                  AND tb.order_product_id IS NULL
                  AND tb.deleted_at IS NULL
            SQL);

            DB::statement(<<<'SQL'
                UPDATE treatment_bookings tb
                INNER JOIN (
                    SELECT op.id AS order_product_id, op.order_id,
                           ROW_NUMBER() OVER (PARTITION BY op.order_id ORDER BY op.id) AS rn
                    FROM order_products op
                    INNER JOIN products p ON p.id = op.product_id AND p.is_virtual = 1
                ) matched ON matched.order_id = tb.order_id AND matched.rn = 1
                SET tb.order_product_id = matched.order_product_id
                WHERE tb.order_id IS NOT NULL
                  AND tb.order_product_id IS NULL
                  AND tb.deleted_at IS NULL
            SQL);
        }
    }

    private function dropOrderIdUniqueOnSlots(): void
    {
        $indexName = $this->findUniqueIndexName('treatment_slot_reservations', 'order_id');

        if (! $indexName) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE treatment_slot_reservations DROP INDEX `{$indexName}`");

            if (! $this->hasIndex('treatment_slot_reservations', 'treatment_slot_order_id_index')) {
                Schema::table('treatment_slot_reservations', function (Blueprint $table): void {
                    $table->index('order_id', 'treatment_slot_order_id_index');
                });
            }

            return;
        }

        Schema::table('treatment_slot_reservations', function (Blueprint $table) use ($indexName): void {
            $table->dropUnique($indexName);
            $table->index('order_id', 'treatment_slot_order_id_index');
        });
    }

    private function rewriteSlotTriggers(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $this->dropSlotTriggers();

        $activeBookings = self::ACTIVE_BOOKING_STATUSES;
        $bookingTime = $this->normalizedTime('NEW.appointment_time');

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER treatment_slot_orders_ai AFTER INSERT ON orders FOR EACH ROW
            BEGIN
                -- no-op: slots are booking-driven
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER treatment_slot_orders_au AFTER UPDATE ON orders FOR EACH ROW
            BEGIN
                -- no-op: slots are booking-driven
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER treatment_slot_orders_ad AFTER DELETE ON orders FOR EACH ROW
            BEGIN
                DELETE FROM treatment_slot_reservations
                WHERE order_id = OLD.id
                  AND treatment_booking_id IS NULL;
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
                    INSERT INTO treatment_slot_reservations
                        (beautician_id, appointment_date, appointment_time, order_id, treatment_booking_id, created_at, updated_at)
                    VALUES
                        (NEW.beautician_id, NEW.appointment_date, {$bookingTime}, NEW.order_id, NEW.id, NOW(), NOW());
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
                    UPDATE treatment_slot_reservations
                    SET beautician_id = NEW.beautician_id,
                        appointment_date = NEW.appointment_date,
                        appointment_time = {$bookingTime},
                        order_id = NEW.order_id,
                        updated_at = NOW()
                    WHERE treatment_booking_id = NEW.id;

                    IF NOT EXISTS (
                        SELECT 1 FROM treatment_slot_reservations WHERE treatment_booking_id = NEW.id
                    ) THEN
                        INSERT INTO treatment_slot_reservations
                            (beautician_id, appointment_date, appointment_time, order_id, treatment_booking_id, created_at, updated_at)
                        VALUES
                            (NEW.beautician_id, NEW.appointment_date, {$bookingTime}, NEW.order_id, NEW.id, NOW(), NOW());
                    END IF;
                ELSE
                    DELETE FROM treatment_slot_reservations WHERE treatment_booking_id = NEW.id;
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER treatment_slot_bookings_ad AFTER DELETE ON treatment_bookings FOR EACH ROW
            BEGIN
                DELETE FROM treatment_slot_reservations WHERE treatment_booking_id = OLD.id;
            END
        SQL);
    }

    private function dropSlotTriggers(): void
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

    private function findUniqueIndexName(string $table, string $column): ?string
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return "{$table}_{$column}_unique";
        }

        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT INDEX_NAME AS index_name
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND NON_UNIQUE = 0
               AND INDEX_NAME != ?
             LIMIT 1',
            [$database, $table, $column, 'PRIMARY']
        );

        return $row->index_name ?? null;
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return false;
        }

        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT 1 AS ok
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?
             LIMIT 1',
            [$database, $table, $indexName]
        );

        return (bool) $row;
    }

    private function normalizedTime(string $expression): string
    {
        return "CASE
            WHEN {$expression} IS NULL OR TRIM({$expression}) = '' THEN NULL
            WHEN {$expression} REGEXP '^[0-9]{1,2}:[0-9]{2}(:[0-9]{2})?$' THEN LEFT(TRIM({$expression}), 5)
            WHEN UPPER(TRIM({$expression})) REGEXP '^[0-9]{1,2}:[0-9]{2}[[:space:]]*[AP]M$' THEN
                DATE_FORMAT(STR_TO_DATE(UPPER(TRIM({$expression})), '%h:%i %p'), '%H:%i')
            ELSE NULL
        END";
    }
};
