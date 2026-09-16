<?php

namespace Tests\Unit\TreatmentReservation;

use Illuminate\Support\Carbon;
use Modules\Beautician\Entities\Beautician;
use Modules\Setting\Repositories\SettingRepository;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Observers\TreatmentBookingObserver;
use Modules\TreatmentReservation\Services\BeauticianAppointmentReminderService;
use Modules\TreatmentReservation\Services\BeauticianTbaReminderService;
use Modules\TreatmentReservation\Services\BeauticianWhatsAppRecipientResolver;
use Modules\User\Entities\User;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Support\PhoneNumber;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BeauticianTbaReminderServiceTest extends TestCase
{
    #[Test]
    public function it_only_allows_tba_reminders_at_or_after_the_configured_daily_time(): void
    {
        $this->withSettings([
            'whatsapp_beautician_tba_reminder_time' => '09:30',
        ], function (): void {
            $service = app(BeauticianTbaReminderService::class);

            $this->assertFalse($service->canSendAt(Carbon::parse('2026-09-14 09:29:59')));
            $this->assertTrue($service->canSendAt(Carbon::parse('2026-09-14 09:30:00')));
            $this->assertTrue($service->canSendAt(Carbon::parse('2026-09-14 16:00:00')));
        });
    }

    #[Test]
    public function it_builds_the_tba_message_with_order_and_portal_context(): void
    {
        $this->withSettings([
            'store_name' => 'IMMA Seri Laris',
            'whatsapp_beautician_tba_reminder_message' => implode("\n", [
                'Hai :beautician',
                'Order #:order_id untuk :customer masih TBA.',
                'Rawatan: :treatment',
                'Rujukan: :reference',
                'Portal: :portal_url',
            ]),
        ], function (): void {
            $beautician = new Beautician();
            $beautician->forceFill([
                'id' => 7,
                'first_name' => 'Jieha',
                'last_name' => 'Mohamed',
            ]);

            $booking = new TreatmentBooking();
            $booking->forceFill([
                'id' => 1584,
                'order_id' => 1401,
                'beautician_id' => 7,
                'customer_first_name' => 'Bambang',
                'customer_last_name' => 'Eka Wijaya',
                'schedule_status' => TreatmentBooking::SCHEDULE_STATUS_TBA,
                'status' => TreatmentBooking::STATUS_PENDING,
            ]);
            $booking->setRelation('beautician', $beautician);
            $booking->setRelation('product', null);

            $message = app(BeauticianTbaReminderService::class)->message($booking);

            $this->assertStringContainsString('Hai Jieha Mohamed', $message);
            $this->assertStringContainsString('Order #1401 untuk Bambang Eka Wijaya masih TBA.', $message);
            $this->assertStringContainsString('Rujukan: B1584', $message);
            $this->assertStringContainsString(route('admin.treatment_reservations.portal'), $message);
        });
    }

    #[Test]
    public function placeholder_beautician_phones_fall_back_to_configured_admin_recipients(): void
    {
        $original = app(OneSenderWhatsAppService::class);
        app()->instance(OneSenderWhatsAppService::class, new class extends OneSenderWhatsAppService {
            public function configuredAdminPhones(): array
            {
                return ['configured-admin'];
            }
        });

        try {
            $user = new User();
            $user->forceFill(['phone' => '60' . '1' . str_repeat('0', 7) . '1']);

            $beautician = new Beautician();
            $beautician->forceFill(['id' => 7, 'phone' => null]);
            $beautician->setRelation('user', $user);

            $booking = new TreatmentBooking();
            $booking->setRelation('beautician', $beautician);

            $this->assertTrue(PhoneNumber::isPlaceholder($user->phone));
            $this->assertSame(
                ['configured-admin'],
                app(BeauticianWhatsAppRecipientResolver::class)->resolve($booking),
            );
        } finally {
            app()->instance(OneSenderWhatsAppService::class, $original);
        }
    }

    #[Test]
    public function moving_a_booking_back_to_tba_rearms_the_reminder(): void
    {
        $booking = new TreatmentBooking();
        $booking->forceFill([
            'status' => TreatmentBooking::STATUS_PENDING,
            'appointment_date' => '2026-09-20',
            'appointment_time' => '10:00:00',
            'schedule_status' => null,
            'beautician_id' => 7,
            'tba_reminder_sent_at' => '2026-09-14 09:30:00',
        ]);
        $booking->syncOriginal();

        $booking->forceFill([
            'appointment_date' => null,
            'appointment_time' => null,
            'schedule_status' => TreatmentBooking::SCHEDULE_STATUS_TBA,
        ]);

        app(TreatmentBookingObserver::class)->updating($booking);

        $this->assertNull($booking->tba_reminder_sent_at);
    }

    #[Test]
    public function manual_appointment_reminders_delegate_tba_bookings_to_the_tba_service(): void
    {
        $booking = new TreatmentBooking();
        $booking->forceFill([
            'id' => 1584,
            'schedule_status' => TreatmentBooking::SCHEDULE_STATUS_TBA,
            'status' => TreatmentBooking::STATUS_PENDING,
        ]);

        $fake = new class extends BeauticianTbaReminderService {
            public ?TreatmentBooking $booking = null;

            public bool $resend = false;


            public function sendManualReminder(TreatmentBooking $booking, bool $resend = false): bool
            {
                $this->booking = $booking;
                $this->resend = $resend;

                return true;
            }
        };
        app()->instance(BeauticianTbaReminderService::class, $fake);

        $sent = app(BeauticianAppointmentReminderService::class)->sendManualReminder($booking, true);

        $this->assertTrue($sent);
        $this->assertSame($booking, $fake->booking);
        $this->assertTrue($fake->resend);
    }

    #[Test]
    public function the_beautician_reminder_action_is_next_to_the_customer_reminder(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 3) . '/modules/TreatmentReservation/Resources/assets/admin/js/kanban-helpers.js'
        );
        $customer = strpos($source, 'data-send-customer-reminder');
        $beautician = strpos($source, 'data-send-beautician-reminder');
        $consultation = strpos($source, 'data-send-consultation');

        $this->assertNotFalse($customer);
        $this->assertNotFalse($beautician);
        $this->assertNotFalse($consultation);
        $this->assertLessThan($beautician, $customer);
        $this->assertLessThan($consultation, $beautician);
    }


    /**
     * @param  array<string, mixed>  $values
     */
    private function withSettings(array $values, callable $callback): void
    {
        $original = app('setting');
        app()->instance('setting', new SettingRepository(collect($values)));

        try {
            $callback();
        } finally {
            app()->instance('setting', $original);
        }
    }
}
