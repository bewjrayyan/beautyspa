<?php

namespace Tests\Feature\TreatmentReservation;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PosBookingHttpSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.url', 'http://localhost');
    }

    #[Test]
    public function unauthenticated_users_cannot_read_or_create_pos_bookings(): void
    {
        $response = $this->getJson("http://localhost/api/bookings");
        $response->assertForbidden();
        self::assertContains($this->postJson("http://localhost/api/bookings", [])->getStatusCode(), [403, 419]);
    }

    #[Test]
    public function unauthenticated_users_cannot_download_private_payment_receipts(): void
    {
        $this->get("http://localhost/admin/treatment-reservations/1/payment-receipt")
            ->assertNotFound();
        $this->get("http://localhost/admin/my/bookings/1/payment-receipt")
            ->assertNotFound();
    }
}
