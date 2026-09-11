<?php

namespace Tests\Unit\TreatmentReservation;

use Illuminate\Support\Facades\Route;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\BookingBeauticianWhatsAppService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingBeauticianWhatsAppServiceTest extends TestCase
{
    #[Test]
    public function it_builds_a_prefilled_reschedule_chat_for_the_assigned_beautician(): void
    {
        app()->setLocale('en');

        $beautician = new Beautician();
        $beautician->forceFill([
            'id' => 1,
            'first_name' => 'Jieha',
            'last_name' => 'Mohamed',
            'phone' => '011-2222 3333',
        ]);

        $booking = new TreatmentBooking();
        $booking->forceFill([
            'id' => 1033,
            'beautician_id' => 1,
            'appointment_date' => '2026-09-11',
            'appointment_time' => '12:00',
            'schedule_status' => null,
        ]);
        $booking->setRelation('beautician', $beautician);

        $url = app(BookingBeauticianWhatsAppService::class)->url($booking);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://wa.me/601122223333?text=', $url);

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $message = (string) ($query['text'] ?? '');

        $this->assertStringContainsString('Jieha Mohamed', $message);
        $this->assertStringContainsString('B1033', $message);
        $this->assertStringContainsString('11 Sep 2026', $message);
        $this->assertStringContainsString('12:00 PM', $message);
    }

    #[Test]
    public function customer_reschedule_routes_are_not_registered(): void
    {
        $this->assertNull(Route::getRoutes()->getByName('treatment_reservations.booking.reschedule'));
        $this->assertNull(Route::getRoutes()->getByName('treatment_reservations.booking.slots'));
        $this->assertNull(Route::getRoutes()->getByName('treatment_reservations.booking.dates'));
    }

    #[Test]
    public function appointment_page_exposes_whatsapp_instead_of_frontend_rescheduling(): void
    {
        $root = dirname(__DIR__, 3);
        $cards = file_get_contents($root . '/modules/TreatmentReservation/Resources/views/public/booking/partials/appointments_cards.blade.php');
        $page = file_get_contents($root . '/modules/TreatmentReservation/Resources/views/public/booking/index.blade.php');
        $english = require $root . '/modules/TreatmentReservation/Resources/lang/en/public.php';

        $this->assertStringContainsString('reschedule_via_whatsapp', $cards);
        $this->assertStringContainsString('account-appointment-card__layout', $cards);
        $this->assertStringContainsString('account-appointment-card__arrival', $cards);
        $this->assertStringContainsString('account-appointment-card__arrival-button', $cards);
        $this->assertSame('View Arrival QR Pass', $english['checkin_pass_open']);
        $this->assertStringNotContainsString('js-reschedule-toggle', $cards);
        $this->assertStringNotContainsString('account-checkin-pass', $cards);
        $this->assertStringNotContainsString('reschedule_form', $cards);
        $this->assertStringNotContainsString('endpoints.reschedule', $page);
    }
}
