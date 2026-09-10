<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Lead\Entities\Lead;
use Modules\Lead\Services\CentralWalletService;
use Modules\Lead\Services\LeadWorkspaceService;
use Modules\Loyalty\Entities\LoyaltyStampWallet;
use Modules\Loyalty\Entities\LoyaltyWallet;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadCentralAuditRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.lead_audit_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::setDefaultConnection('lead_audit_test');
        Schema::create('users', function (Blueprint $t): void { $t->id(); $t->string('phone')->nullable(); });
        Schema::create('leads', function (Blueprint $t): void {
            $t->id();
            foreach (['name', 'phone', 'email', 'source', 'status'] as $column) $t->string($column)->nullable();
            foreach (['spa_branch_id', 'beautician_id', 'customer_id'] as $column) $t->integer($column)->nullable();
            $t->boolean('is_duplicate')->default(false); $t->boolean('is_existing_customer')->default(false);
            $t->timestamps(); $t->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        DB::purge('lead_audit_test');
        parent::tearDown();
    }

    private function pair(): void
    {
        foreach ([false, true] as $duplicate) {
            DB::table('leads')->insert(['name' => 'Fixture', 'phone' => '60123456789', 'status' => 'new', 'source' => 'manual', 'is_duplicate' => $duplicate]);
        }
    }

    #[Test]
    public function renaming_original_and_duplicate_preserves_their_identity(): void
    {
        $this->pair();
        $service = app(LeadWorkspaceService::class);
        foreach ([1, 2] as $id) {
            Lead::withoutEvents(fn () => $service->update(Lead::findOrFail($id), ['name' => 'Renamed', 'phone' => '+60123456789']));
        }
        $this->assertFalse(Lead::find(1)->is_duplicate);
        $this->assertTrue(Lead::find(2)->is_duplicate);
        $this->assertSame(1, $service->summary()['unique']);
    }

    #[Test]
    public function changing_phone_recalculates_duplicate_status(): void
    {
        $this->pair();
        $service = app(LeadWorkspaceService::class);
        Lead::withoutEvents(fn () => $service->update(Lead::findOrFail(2), ['name' => 'Fixture', 'phone' => '60199887766']));
        $this->assertFalse(Lead::find(2)->is_duplicate);
        $this->assertSame(2, $service->summary()['unique']);
        Lead::withoutEvents(fn () => $service->update(Lead::findOrFail(2), ['name' => 'Fixture', 'phone' => '60123456789']));
        $this->assertTrue(Lead::find(2)->is_duplicate);
        $this->assertSame(1, $service->summary()['unique']);
    }

    #[Test]
    public function wallet_active_stamp_count_matches_loyalty_lifecycle(): void
    {
        Schema::create('loyalty_stamp_wallets', function (Blueprint $t): void {
            $t->id(); $t->integer('user_id');
            foreach (['completed_at', 'redeemed_at', 'fulfilled_at', 'expires_at'] as $column) $t->timestamp($column)->nullable();
        });
        foreach ([['completed_at' => now()], ['redeemed_at' => now()], ['expires_at' => now()->subDay()], ['expires_at' => now()->addDay()]] as $row) {
            DB::table('loyalty_stamp_wallets')->insert(['user_id' => 1] + $row);
        }
        $user = new class {
            public int $id = 1;
            public string $full_name = 'Fixture';
            public string $phone = '';
            public string $email = '';
            public function avatarUrl(): ?string { return null; }
        };
        $wallet = new LoyaltyWallet(['user_id' => 1, 'balance' => 0]);
        $wallet->setRelation('user', $user)->setRelation('tier', null)->setRelation('transactions', collect());
        $payload = app(CentralWalletService::class)->toArray($wallet);
        $this->assertSame(1, $payload['stamp_active']);
        $this->assertSame(LoyaltyStampWallet::all()->filter(fn ($stamp) => $stamp->isActive())->count(), $payload['stamp_active']);
    }
}
