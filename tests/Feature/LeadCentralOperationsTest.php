<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Modules\Lead\Http\Controllers\Admin\CentralCheckinController;
use Modules\Lead\Services\CentralCheckinService;
use Modules\Lead\Services\CentralClearanceService;
use Modules\Lead\Services\CentralWalletService;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\Setting\Repositories\SettingRepository;
use Modules\Support\Http\Middleware\SecurityHeaders;
use Modules\TreatmentReservation\Mail\AppointmentCheckinReminder;
use Modules\TreatmentReservation\Services\BookingCheckinPassService;
use Modules\TreatmentReservation\Services\CustomerAppointmentReminderService;
use Modules\TreatmentReservation\Services\TreatmentBookingActivityLogger;
use Modules\User\Services\OneSenderWhatsAppService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadCentralOperationsTest extends TestCase
{
    private int $customerRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerRole = (int) setting('customer_role');
        config(['database.connections.lead_operations_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::setDefaultConnection('lead_operations_test');
        $this->travelTo(now()->setDate(2026, 9, 9)->setTime(12, 0));
        Schema::create('treatment_bookings', function (Blueprint $table): void {
            $table->id();
            foreach (['order_id', 'product_id', 'beautician_id', 'customer_id', 'spa_branch_id'] as $column) {
                $table->unsignedBigInteger($column)->nullable();
            }
            foreach (['source', 'status', 'payment_status', 'customer_first_name', 'customer_last_name', 'customer_phone', 'customer_email', 'appointment_time'] as $column) {
                $table->string($column)->nullable();
            }
            $table->date('appointment_date');
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('customer_reminder_sent_at')->nullable();
            $table->dateTime('customer_email_reminder_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('treatment_booking_activities', function (Blueprint $table): void {
            $table->id();
            $table->integer('treatment_booking_id');
            $table->integer('user_id')->nullable();
            $table->string('action');
            $table->string('from_value')->nullable();
            $table->string('to_value')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('payment_status')->nullable();
            $table->unsignedBigInteger('spa_branch_id')->nullable();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        URL::forceRootUrl(null);
        $this->travelBack();
        DB::purge('lead_operations_test');
        parent::tearDown();
    }

    private function booking(array $attributes = []): int
    {
        return DB::table('treatment_bookings')->insertGetId(array_replace([
            'source' => 'admin_manual', 'status' => 'pending', 'payment_status' => 'full_paid',
            'appointment_date' => '2026-09-09', 'appointment_time' => '10:00',
            'customer_first_name' => 'Fixture', 'updated_at' => now(), 'created_at' => now(),
        ], $attributes));
    }

    #[Test]
    public function automatic_reminder_emails_the_secure_checkin_pass_one_day_before_appointment(): void
    {
        Mail::fake();
        URL::forceRootUrl('http://localhost');
        $originalSettings = app('setting');
        app()->instance('setting', new SettingRepository(collect([
            'store_name' => 'Test Spa',
            'whatsapp_customer_reminder_enabled' => false,
            'whatsapp_customer_reminder_minutes' => 1440,
        ])));

        try {
            $bookingId = $this->booking([
                'appointment_date' => '2026-09-10',
                'appointment_time' => '12:00',
                'customer_email' => 'customer@example.test',
            ]);

            $booking = TreatmentBooking::findOrFail($bookingId);
            $service = app(CustomerAppointmentReminderService::class);
            $messageBuilder = new \ReflectionMethod($service, 'buildMessage');
            $checkinUrl = app(BookingCheckinPassService::class)->url($booking);
            $whatsAppMessage = $messageBuilder->invoke($service, $booking);

            $this->assertStringContainsString($booking->referenceCode(), $whatsAppMessage);
            $this->assertStringContainsString($checkinUrl, $whatsAppMessage);
            $this->assertSame(1, $service->sendDueReminders());

            Mail::assertSent(AppointmentCheckinReminder::class, function (AppointmentCheckinReminder $mail) use ($bookingId): bool {
                $html = $mail->render();

                return $mail->hasTo('customer@example.test')
                    && Request::create(
                        aestheticcart_strip_install_base_from_url($mail->checkinUrl)
                    )->hasValidSignature(absolute: false)
                    && str_contains($html, 'B'.$bookingId)
                    && str_contains($html, e($mail->checkinUrl));
            });
            $this->assertNotNull(DB::table('treatment_bookings')->where('id', $bookingId)->value('customer_email_reminder_sent_at'));
            $this->assertNull(DB::table('treatment_bookings')->where('id', $bookingId)->value('customer_reminder_sent_at'));
            $this->assertSame(1, DB::table('treatment_booking_activities')->where('action', 'email_reminder_sent')->count());

            $this->assertSame(0, $service->sendDueReminders());
            Mail::assertSentCount(1);
        } finally {
            app()->instance('setting', $originalSettings);
        }
    }

    #[Test]
    public function a_delivered_email_is_not_retried_when_activity_logging_fails(): void
    {
        Mail::fake();
        $originalSettings = app('setting');
        app()->instance('setting', new SettingRepository(collect([
            'store_name' => 'Test Spa',
            'whatsapp_customer_reminder_enabled' => false,
            'whatsapp_customer_reminder_minutes' => 1440,
        ])));
        $logger = new class extends TreatmentBookingActivityLogger {
            public function logEmailReminderSent(TreatmentBooking $booking): void
            {
                throw new \RuntimeException('audit unavailable');
            }
        };
        app()->instance(TreatmentBookingActivityLogger::class, $logger);

        try {
            $bookingId = $this->booking([
                'appointment_date' => '2026-09-10',
                'appointment_time' => '12:00',
                'customer_email' => 'customer@example.test',
            ]);
            $service = app(CustomerAppointmentReminderService::class);

            $this->assertSame(1, $service->sendDueReminders());
            $this->assertNotNull(DB::table('treatment_bookings')->where('id', $bookingId)->value('customer_email_reminder_sent_at'));
            $this->assertSame(0, $service->sendDueReminders());
            Mail::assertSentCount(1);
        } finally {
            app()->instance('setting', $originalSettings);
        }
    }

    #[Test]
    public function an_explicit_whatsapp_resend_uses_a_fresh_dedupe_key(): void
    {
        $originalWhatsapp = app(OneSenderWhatsAppService::class);
        $whatsapp = new class extends OneSenderWhatsAppService {
            /** @var array<int, array<string, mixed>> */
            public array $contexts = [];

            public function sendNotification(string $phone, string $message, array $context = []): bool
            {
                $this->contexts[] = $context;

                return true;
            }
        };
        app()->instance(OneSenderWhatsAppService::class, $whatsapp);

        try {
            $booking = TreatmentBooking::findOrFail($this->booking([
                'appointment_date' => '2026-09-10',
                'appointment_time' => '12:00',
                'customer_phone' => '60123456789',
            ]));
            $service = app(CustomerAppointmentReminderService::class);
            $deliver = new \ReflectionMethod($service, 'deliverReminder');

            $this->assertTrue($deliver->invoke($service, $booking, true));
            TreatmentBooking::query()->whereKey($booking->id)->update(['customer_reminder_sent_at' => null]);
            $this->travel(1)->seconds();
            $this->assertTrue($deliver->invoke($service, $booking->fresh(), true));

            $this->assertCount(2, $whatsapp->contexts);
            $this->assertNotSame(
                $whatsapp->contexts[0]['dedupe_key'],
                $whatsapp->contexts[1]['dedupe_key'],
            );
            $this->assertStringContainsString(':manual:', $whatsapp->contexts[0]['dedupe_key']);
            $this->assertNotNull($booking->fresh()->customer_reminder_sent_at);
        } finally {
            app()->instance(OneSenderWhatsAppService::class, $originalWhatsapp);
        }
    }

    #[Test]
    public function clearance_paginates_beyond_500_and_counts_the_full_queue(): void
    {
        for ($i = 0; $i < 505; $i++) {
            $this->booking(['checked_in_at' => now()]);
        }
        $this->booking(['payment_status' => 'deposit', 'checked_in_at' => now()]);
        $this->booking(['status' => 'in_progress']);
        $this->booking(['status' => 'canceled']);
        $service = app(CentralClearanceService::class);
        $this->assertSame(505, $service->paginate(['state' => 'waiting'])->total());
        $this->assertSame(1, $service->paginate(['state' => 'blocked'])->total());
        $this->assertSame(507, $service->paginate(['state' => 'all_queue'])->total());
        $this->assertSame(507, $service->summary()['queue']);
        request()->query->set('page', 21);
        $last = $service->paginate(['state' => 'waiting']);
        $this->assertCount(5, $last->items());
        $this->assertSame(21, $last->lastPage());
    }

    #[Test]
    public function clearance_honors_manual_and_order_payment_sources(): void
    {
        DB::table('orders')->insert(['id' => 1, 'payment_status' => 'pending']);
        DB::table('orders')->insert(['id' => 2, 'payment_status' => 'paid']);
        $ready = $this->booking(['source' => 'checkout', 'order_id' => 2, 'payment_status' => 'pending', 'checked_in_at' => now()]);
        $blocked = $this->booking(['source' => 'checkout', 'order_id' => 1, 'payment_status' => 'paid', 'checked_in_at' => now()]);
        $manual = $this->booking(['payment_status' => 'paid', 'checked_in_at' => now()]);
        $service = app(CentralClearanceService::class);
        $this->assertEqualsCanonicalizing([$ready, $manual], collect($service->paginate(['state' => 'waiting'])->items())->pluck('id')->all());
        $this->assertSame([$blocked], collect($service->paginate(['state' => 'blocked'])->items())->pluck('id')->all());
    }

    #[Test]
    public function clearance_queue_starts_only_after_customer_checkin(): void
    {
        $notArrived = $this->booking();
        $arrived = $this->booking(['checked_in_at' => now()]);
        $service = app(CentralClearanceService::class);

        $this->assertSame([$arrived], collect($service->paginate(['state' => 'waiting'])->items())->pluck('id')->all());
        $this->assertSame(1, $service->summary()['queue']);
        $this->assertNotContains($notArrived, collect($service->paginate(['state' => 'all_queue'])->items())->pluck('id')->all());
    }

    #[Test]
    public function clearance_enforces_audited_forward_only_treatment_transitions(): void
    {
        $service = app(CentralClearanceService::class);
        $bookingId = $this->booking(['checked_in_at' => now()]);

        $started = $service->transition($bookingId, TreatmentBooking::STATUS_IN_PROGRESS);
        $this->assertSame(TreatmentBooking::STATUS_IN_PROGRESS, $started->status);
        $this->assertSame(1, DB::table('treatment_booking_activities')->where('action', 'status_changed')->count());

        $service->transition($bookingId, TreatmentBooking::STATUS_IN_PROGRESS);
        $this->assertSame(1, DB::table('treatment_booking_activities')->where('action', 'status_changed')->count());

        $completed = $service->transition($bookingId, TreatmentBooking::STATUS_COMPLETED);
        $this->assertSame(TreatmentBooking::STATUS_COMPLETED, $completed->status);
        $this->assertSame(2, DB::table('treatment_booking_activities')->where('action', 'status_changed')->count());
    }

    #[Test]
    public function clearance_rejects_starting_before_checkin_or_payment_clearance(): void
    {
        $service = app(CentralClearanceService::class);
        $notArrived = $this->booking();
        $blocked = $this->booking(['checked_in_at' => now(), 'payment_status' => 'deposit']);

        foreach ([$notArrived, $blocked] as $bookingId) {
            try {
                $service->transition($bookingId, TreatmentBooking::STATUS_IN_PROGRESS);
                $this->fail('Unsafe treatment start was accepted.');
            } catch (\DomainException) {
                $this->assertSame(TreatmentBooking::STATUS_PENDING, TreatmentBooking::findOrFail($bookingId)->status);
            }
        }

        $this->assertSame(0, DB::table('treatment_booking_activities')->count());
    }

    #[Test]
    public function checkin_tabs_and_summaries_use_the_same_date_and_search_scope(): void
    {
        $this->booking(['customer_first_name' => 'Selected', 'checked_in_at' => now()->subMinutes(10)]);
        $this->booking(['customer_first_name' => 'Selected', 'status' => 'completed']);
        $this->booking(['customer_first_name' => 'Elsewhere']);
        $this->booking(['customer_first_name' => 'Selected', 'appointment_date' => '2026-09-10', 'checked_in_at' => now()]);
        $service = app(CentralCheckinService::class);
        $filters = ['date' => '2026-09-09', 'scope' => 'day', 'q' => 'Selected'];
        $this->assertSame(1, $service->paginate($filters + ['status' => 'live'])->total());
        $this->assertSame(2, $service->paginate($filters + ['status' => 'all'])->total());
        $this->assertSame(1, $service->paginate($filters + ['status' => 'waiting'])->total());
        $this->assertSame(1, $service->paginate($filters + ['status' => 'completed'])->total());
        $summary = $service->summary($filters);
        $this->assertSame(1, $summary['live']);
        $this->assertSame(0, $summary['scheduled']);
        $this->assertSame(1, $summary['waiting']);
        $this->assertSame(1, $summary['completed']);
        $this->assertSame(2, $service->summary(array_replace($filters, ['scope' => 'pipeline']))['waiting']);
    }

    #[Test]
    public function checkin_exposes_not_arrived_bookings_without_mixing_arrival_and_treatment_states(): void
    {
        $todayBooked = $this->booking(['customer_first_name' => 'Today booked']);
        $todayArrived = $this->booking(['customer_first_name' => 'Today arrived', 'checked_in_at' => now()]);
        $futureBooked = $this->booking(['customer_first_name' => 'Future booked', 'appointment_date' => '2026-09-10']);
        $inTreatment = $this->booking(['customer_first_name' => 'In treatment', 'status' => 'in_progress']);
        $service = app(CentralCheckinService::class);

        $dayFilters = ['date' => '2026-09-09', 'scope' => 'day'];
        $this->assertSame([$todayBooked], collect($service->paginate($dayFilters + ['status' => 'booked'])->items())->pluck('id')->all());
        $this->assertSame(1, $service->summary($dayFilters)['scheduled']);
        $this->assertEqualsCanonicalizing(
            [$todayBooked, $futureBooked],
            collect($service->paginate(['scope' => 'pipeline', 'status' => 'booked'])->items())->pluck('id')->all(),
        );

        $actionFor = function (int $bookingId) use ($service): array {
            $booking = TreatmentBooking::findOrFail($bookingId);
            $booking->setRelation('product', null);
            $booking->setRelation('beautician', null);
            $booking->setRelation('order', null);
            $booking->setRelation('customer', null);

            return $service->toArray($booking)['available_actions'];
        };

        $this->assertSame(['confirm_arrival'], $actionFor($todayBooked));
        $this->assertSame(['open_clearance'], $actionFor($todayArrived));
        $this->assertSame([], $actionFor($futureBooked));
        $this->assertSame(['open_crm'], $actionFor($inTreatment));
    }

    #[Test]
    public function confirming_arrival_is_idempotent_and_does_not_start_treatment(): void
    {
        $bookingId = $this->booking();
        $request = Request::create('/admin/leads/checkin/'.$bookingId.'/confirm', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $first = app(CentralCheckinController::class)->confirm($request, $bookingId);
        $second = app(CentralCheckinController::class)->confirm($request, $bookingId);

        $this->assertSame(200, $first->getStatusCode());
        $this->assertSame(200, $second->getStatusCode());
        $this->assertSame('pending', DB::table('treatment_bookings')->where('id', $bookingId)->value('status'));
        $this->assertNotNull(DB::table('treatment_bookings')->where('id', $bookingId)->value('checked_in_at'));
        $this->assertSame(1, DB::table('treatment_booking_activities')->where('action', 'checked_in')->count());
    }

    #[Test]
    public function arrival_pass_is_signed_and_arrival_rejects_another_date(): void
    {
        $bookingId = $this->booking(['appointment_date' => '2026-09-10']);
        $booking = TreatmentBooking::findOrFail($bookingId);
        URL::forceRootUrl('http://localhost');
        $url = app(BookingCheckinPassService::class)->url($booking);

        $internalUrl = aestheticcart_strip_install_base_from_url($url);
        $this->assertTrue(Request::create($internalUrl)->hasValidSignature(absolute: false));
        $this->get($internalUrl)
            ->assertOk()
            ->assertSee('data-checkin-pass=', false)
            ->assertSee('modules/lead/central/qrcode.js', false);
        $this->get($internalUrl.'&tampered=1')->assertForbidden();


        $request = Request::create('/admin/leads/checkin/'.$bookingId.'/confirm', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $response = app(CentralCheckinController::class)->confirm($request, $bookingId);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertNull(DB::table('treatment_bookings')->where('id', $bookingId)->value('checked_in_at'));
    }

    #[Test]
    public function camera_is_allowed_only_for_the_lead_central_workspace(): void
    {
        $middleware = app(SecurityHeaders::class);
        $central = $middleware->handle(
            Request::create('/admin/leads/central/checkin'),
            fn () => response('ok')
        );
        $otherAdmin = $middleware->handle(
            Request::create('/admin/orders'),
            fn () => response('ok')
        );

        $this->assertStringStartsWith('camera=(self)', (string) $central->headers->get('Permissions-Policy'));
        $this->assertStringStartsWith('camera=()', (string) $otherAdmin->headers->get('Permissions-Policy'));
    }

    #[Test]
    public function checkin_branch_filter_uses_the_order_branch_for_legacy_bookings(): void
    {
        DB::table('orders')->insert(['id' => 30, 'payment_status' => 'paid', 'spa_branch_id' => 7]);
        $bookingId = $this->booking([
            'source' => 'checkout',
            'order_id' => 30,
            'spa_branch_id' => null,
            'checked_in_at' => now(),
        ]);

        $items = collect(app(CentralCheckinService::class)->paginate([
            'date' => '2026-09-09',
            'status' => 'waiting',
            'branch' => 7,
        ])->items());

        $this->assertSame([$bookingId], $items->pluck('id')->all());
    }

    #[Test]
    public function clearance_uses_completion_activity_not_an_unrelated_edit_timestamp(): void
    {
        $old = $this->booking(['status' => 'completed']);
        $today = $this->booking(['status' => 'completed']);
        DB::table('treatment_booking_activities')->insert([
            ['treatment_booking_id' => $old, 'action' => 'status_changed', 'to_value' => 'completed', 'created_at' => now()->subDay()],
            ['treatment_booking_id' => $today, 'action' => 'status_changed', 'to_value' => 'completed', 'created_at' => now()],
        ]);
        $service = app(CentralClearanceService::class);
        $this->assertSame(1, $service->summary()['done_today']);
        $this->assertSame([$today], collect($service->paginate(['state' => 'done'])->items())->pluck('id')->all());
    }

    #[Test]
    public function wallet_summary_counts_customers_with_ready_stamps_not_cards(): void
    {
        Schema::create('users', function (Blueprint $table): void { $table->id(); });
        Schema::create('roles', function (Blueprint $table): void { $table->id(); });
        Schema::create('user_roles', function (Blueprint $table): void { $table->integer('role_id'); $table->integer('user_id'); });
        Schema::create('loyalty_wallets', function (Blueprint $table): void {
            $table->id(); $table->integer('user_id'); $table->integer('tier_id')->nullable(); $table->integer('balance');
        });
        Schema::create('loyalty_stamp_wallets', function (Blueprint $table): void {
            $table->id(); $table->integer('user_id'); $table->timestamp('redeemed_at')->nullable(); $table->timestamp('fulfilled_at')->nullable();
        });
        DB::table('roles')->insert(['id' => $this->customerRole]);
        DB::table('users')->insert([['id' => 1], ['id' => 2], ['id' => 3]]);
        DB::table('user_roles')->insert([['role_id' => $this->customerRole, 'user_id' => 1], ['role_id' => $this->customerRole, 'user_id' => 2]]);
        DB::table('loyalty_wallets')->insert([
            ['id' => 1, 'user_id' => 1, 'balance' => 100], ['id' => 2, 'user_id' => 2, 'balance' => 0], ['id' => 3, 'user_id' => 3, 'balance' => 500],
        ]);
        DB::table('loyalty_stamp_wallets')->insert([['user_id' => 1, 'redeemed_at' => now()], ['user_id' => 1, 'redeemed_at' => now()]]);
        $service = app(CentralWalletService::class);
        $summary = $service->summary();
        $this->assertSame(2, $summary['members']);
        $this->assertSame(100, $summary['points_outstanding']);
        $this->assertSame(1, $summary['stamp_ready']);
        $this->assertSame(0, $service->summary(['customer_id' => 2])['stamp_ready']);
        $this->assertSame(1, $service->summary(['customer_id' => 2])['members']);
        Schema::create('loyalty_tiers', function (Blueprint $table): void { $table->id(); $table->string('name'); });
        Schema::create('loyalty_transactions', function (Blueprint $table): void {
            $table->id(); $table->integer('wallet_id'); $table->integer('points'); $table->timestamps();
        });
        foreach ([1, 2] as $walletId) {
            for ($i = 0; $i < 7; $i++) {
                DB::table('loyalty_transactions')->insert(['wallet_id' => $walletId, 'points' => 10, 'created_at' => now()->subMinutes($i)]);
            }
        }
        $wallets = $service->paginate()->items();
        $this->assertCount(2, $wallets);
        foreach ($wallets as $wallet) {
            $this->assertTrue($wallet->relationLoaded('transactions'));
            $this->assertCount(5, $wallet->transactions);
            $this->assertSame(7, $wallet->transactions_count);
        }
        Schema::drop('loyalty_stamp_wallets');
        $this->assertSame(0, $service->summary()['stamp_ready']);
    }
}
