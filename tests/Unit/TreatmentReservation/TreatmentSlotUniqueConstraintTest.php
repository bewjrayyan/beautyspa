<?php

namespace Tests\Unit\TreatmentReservation;

use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TreatmentSlotUniqueConstraintTest extends TestCase
{
    #[Test]
    public function database_constraint_rejects_two_owners_for_the_same_beautician_slot(): void
    {
        $database = new PDO('sqlite::memory:');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $database->exec(<<<'SQL'
            CREATE TABLE treatment_slot_reservations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                beautician_id INTEGER NOT NULL,
                appointment_date TEXT NOT NULL,
                appointment_time TEXT NOT NULL,
                order_id INTEGER NULL UNIQUE,
                treatment_booking_id INTEGER NULL UNIQUE,
                UNIQUE (beautician_id, appointment_date, appointment_time)
            )
        SQL);

        $insert = $database->prepare(<<<'SQL'
            INSERT INTO treatment_slot_reservations
                (beautician_id, appointment_date, appointment_time, order_id, treatment_booking_id)
            VALUES (?, ?, ?, ?, ?)
        SQL);

        $insert->execute([7, '2026-08-01', '14:30', 101, null]);

        $this->expectException(PDOException::class);
        $insert->execute([7, '2026-08-01', '14:30', null, 202]);
    }

    #[Test]
    public function different_beauticians_or_times_remain_independent_slots(): void
    {
        $database = new PDO('sqlite::memory:');
        $database->exec(<<<'SQL'
            CREATE TABLE slots (
                beautician_id INTEGER NOT NULL,
                appointment_date TEXT NOT NULL,
                appointment_time TEXT NOT NULL,
                UNIQUE (beautician_id, appointment_date, appointment_time)
            )
        SQL);

        $insert = $database->prepare(
            'INSERT INTO slots (beautician_id, appointment_date, appointment_time) VALUES (?, ?, ?)'
        );
        $insert->execute([7, '2026-08-01', '14:30']);
        $insert->execute([8, '2026-08-01', '14:30']);
        $insert->execute([7, '2026-08-01', '15:00']);

        $this->assertSame(3, (int) $database->query('SELECT COUNT(*) FROM slots')->fetchColumn());
    }
}
