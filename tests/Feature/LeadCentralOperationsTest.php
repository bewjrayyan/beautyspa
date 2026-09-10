<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Lead\Services\CentralCheckinService;
use Modules\Lead\Services\CentralClearanceService;
use Modules\Lead\Services\CentralWalletService;
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
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('treatment_booking_activities', function (Blueprint $table): void {
            $table->id(); $table->integer('treatment_booking_id'); $table->string('action'); $table->string('to_value'); $table->timestamps();
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
    public function clearance_paginates_beyond_500_and_counts_the_full_queue(): void
    {
        for ($i = 0; $i < 505; $i++) {
            $this->booking();
        }
        $this->booking(['payment_status' => 'deposit']);
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
        $ready = $this->booking(['source' => 'checkout', 'order_id' => 2, 'payment_status' => 'pending']);
        $blocked = $this->booking(['source' => 'checkout', 'order_id' => 1, 'payment_status' => 'paid']);
        $manual = $this->booking(['payment_status' => 'paid']);
        $service = app(CentralClearanceService::class);
        $this->assertEqualsCanonicalizing([$ready, $manual], collect($service->paginate(['state' => 'waiting'])->items())->pluck('id')->all());
        $this->assertSame([$blocked], collect($service->paginate(['state' => 'blocked'])->items())->pluck('id')->all());
    }

    #[Test]
    public function checkin_tabs_and_summaries_use_the_same_date_and_search_scope(): void
    {
        $this->booking(['customer_first_name' => 'Selected']);
        $this->booking(['customer_first_name' => 'Selected', 'status' => 'completed']);
        $this->booking(['customer_first_name' => 'Elsewhere']);
        $this->booking(['customer_first_name' => 'Selected', 'appointment_date' => '2026-09-10']);
        $service = app(CentralCheckinService::class);
        $filters = ['date' => '2026-09-09', 'scope' => 'day', 'q' => 'Selected'];
        $this->assertSame(2, $service->paginate($filters + ['status' => 'all'])->total());
        $this->assertSame(1, $service->paginate($filters + ['status' => 'completed'])->total());
        $summary = $service->summary($filters);
        $this->assertSame(1, $summary['live']);
        $this->assertSame(1, $summary['waiting']);
        $this->assertSame(1, $summary['completed']);
        $this->assertSame(2, $service->summary(array_replace($filters, ['scope' => 'pipeline']))['waiting']);
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
        }
        Schema::drop('loyalty_stamp_wallets');
        $this->assertSame(0, $service->summary()['stamp_ready']);
    }
}
