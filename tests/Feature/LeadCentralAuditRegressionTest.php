<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Modules\Lead\Entities\Lead;
use Modules\Lead\Http\Requests\Admin\BulkUpdateLeadsRequest;
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
        Schema::create('users', function (Blueprint $t): void {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('phone')->nullable();
        });
        Schema::create('spa_branches', function (Blueprint $t): void {
            $t->id();
            $t->string('name')->nullable();
            $t->string('code')->nullable();
        });
        Schema::create('beauticians', function (Blueprint $t): void {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
        });
        Schema::create('leads', function (Blueprint $t): void {
            $t->id();
            foreach (['name', 'phone', 'email', 'source', 'status'] as $column) $t->string($column)->nullable();
            foreach (['spa_branch_id', 'beautician_id', 'customer_id'] as $column) $t->integer($column)->nullable();
            $t->boolean('is_duplicate')->default(false); $t->boolean('is_existing_customer')->default(false);
            $t->timestamp('last_followed_up_at')->nullable();
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
    public function lead_directory_supports_only_the_requested_page_sizes(): void
    {
        foreach (range(1, 205) as $index) {
            DB::table('leads')->insert([
                'name' => "Lead {$index}",
                'phone' => "6012{$index}",
                'status' => 'new',
                'source' => 'manual',
            ]);
        }

        $service = app(LeadWorkspaceService::class);
        $largestPage = $service->paginate(['per_page' => 200]);
        $invalidPage = $service->paginate(['per_page' => 999]);

        $this->assertSame(200, $largestPage->perPage());
        $this->assertCount(200, $largestPage->items());
        $this->assertSame(205, $largestPage->total());
        $this->assertSame(10, $invalidPage->perPage());
        $this->assertCount(10, $invalidPage->items());
    }

    #[Test]
    public function lead_kpis_return_selected_month_and_all_time_totals(): void
    {
        DB::table('leads')->insert([
            ['name' => 'August lead', 'phone' => '60120000001', 'status' => Lead::STATUS_NEW, 'source' => 'manual', 'is_duplicate' => false, 'is_existing_customer' => false, 'created_at' => '2026-08-10 09:00:00'],
            ['name' => 'September existing', 'phone' => '60120000002', 'status' => Lead::STATUS_CONVERTED, 'source' => 'manual', 'is_duplicate' => false, 'is_existing_customer' => true, 'created_at' => '2026-09-10 09:00:00'],
            ['name' => 'September duplicate', 'phone' => '60120000002', 'status' => Lead::STATUS_NEW, 'source' => 'manual', 'is_duplicate' => true, 'is_existing_customer' => false, 'created_at' => '2026-09-11 09:00:00'],
        ]);

        $summary = app(LeadWorkspaceService::class)->summary(null, '2026-09');

        $this->assertSame(2, $summary['raw']);
        $this->assertSame(1, $summary['unique']);
        $this->assertSame(1, $summary['existing']);
        $this->assertSame(100.0, $summary['conversion_pct']);
        $this->assertSame(3, $summary['all_time']['raw']);
        $this->assertSame(2, $summary['all_time']['unique']);
        $this->assertSame(1, $summary['all_time']['existing']);
        $this->assertSame(50.0, $summary['all_time']['conversion_pct']);
    }

    #[Test]
    public function lead_directory_uses_page_size_controls_without_an_inner_scrollbar(): void
    {
        $script = file_get_contents(public_path('modules/lead/central/app.js'));
        $styles = file_get_contents(public_path('modules/lead/central/styles.css'));

        $this->assertStringContainsString('const pageSizes=[10,50,100,200]', $script);
        $this->assertStringContainsString("qs.set('per_page', String(state.leadPerPage||10))", $script);
        $this->assertStringContainsString('.lead-table-wrap{border-radius:0;max-height:none;overflow:visible', $styles);
        $this->assertStringContainsString('.lead-table{width:100%;min-width:0;table-layout:fixed', $styles);
        $this->assertStringContainsString("['created_at',t('workspace.bulk_update_date')]", $script);
        $this->assertStringContainsString("['source',t('workspace.bulk_update_source')]", $script);
        $this->assertStringContainsString("if(action==='source')", $script);
        $this->assertStringContainsString('id="leadBulkValue" type="date"', $script);
        $this->assertStringContainsString('.lead-bulk-date{appearance:auto', $styles);
        $this->assertStringContainsString('const all=s.all_time||s', $script);
        $this->assertStringContainsString("leadKpi(leadKpiIcon('database'),t('workspace.total_leads_database')", $script);
        $this->assertStringContainsString("t('workspace.unit_lead_records')", $script);
        $this->assertStringContainsString("leadKpi(leadKpiIcon('customers'),t('workspace.existing_customers')", $script);
        $this->assertStringContainsString('.lead-kpi-card--teal{--lead-kpi-accent:#0891b2', $styles);
    }

    #[Test]
    public function overview_uses_ranked_conversion_bars_with_complete_beautician_metrics(): void
    {
        $script = file_get_contents(public_path('modules/lead/central/app.js'));
        $charts = file_get_contents(public_path('modules/lead/central/trade-charts.js'));

        $this->assertStringContainsString("tradePane('trade.conversion_title','trade.conversion_sub','tradeConversion')", $script);
        $this->assertStringContainsString('converted:b.converted||0', $script);
        $this->assertStringContainsString('function conversionBars(el, rows)', $charts);
        $this->assertStringContainsString("conversionBars(document.getElementById('tradeConversion')", $charts);
        $this->assertStringContainsString("name: t('trade.converted_customers')", $charts);
        $this->assertStringNotContainsString("type: 'scatter'", $charts);
        $this->assertStringContainsString("view==='beauticians'?'methodology_beauticians':'methodology_branches'", $script);
    }

    #[Test]
    public function notification_center_supports_scoped_confirmed_message_clearing(): void
    {
        $script = file_get_contents(public_path('modules/lead/central/app.js'));
        $styles = file_get_contents(public_path('modules/lead/central/styles.css'));
        $view = file_get_contents(base_path('modules/Lead/Resources/views/admin/central/index.blade.php'));
        $controller = file_get_contents(base_path('modules/Lead/Http/Controllers/Admin/CentralDashboardController.php'));

        $this->assertStringContainsString('function notificationSignature(item)', $script);
        $this->assertStringContainsString('`${notificationScope()}|${item.id}|${item.count}`', $script);
        $this->assertStringContainsString('function requestClearNotifications()', $script);
        $this->assertStringContainsString("openActionDrawer(t('notifications.clear_title')", $script);
        $this->assertStringContainsString('dismissNotificationItems(items)', $script);
        $this->assertStringContainsString('data-clear-notifications', $script);
        $this->assertStringContainsString('if(metricsStale) refreshMetrics()', $script);
        $this->assertStringContainsString('aria-haspopup="dialog"', $view);
        $this->assertStringContainsString('notificationStorageKey: @json($notificationStorageKey)', $view);
        $this->assertStringContainsString('id="toast" role="status" aria-live="polite"', $view);
        $this->assertStringContainsString('imma-central.notifications.v1.', $controller);
        $this->assertStringContainsString('.notification-menu__clear:focus-visible', $styles);
        $this->assertStringContainsString('@media(max-width:420px){.notification-menu{position:fixed', $styles);
    }

    #[Test]
    public function clearance_exposes_permission_aware_crm_actions_and_a_responsive_flow(): void
    {
        $script = file_get_contents(public_path('modules/lead/central/app.js'));
        $styles = file_get_contents(public_path('modules/lead/central/styles.css'));
        $view = file_get_contents(base_path('modules/Lead/Resources/views/admin/central/index.blade.php'));
        $routes = file_get_contents(base_path('modules/Lead/Routes/admin.php'));

        $this->assertStringContainsString('function clearancePrimaryAction(c,compact=false)', $script);
        $this->assertStringContainsString("actions.includes('start_treatment') && boot.canEditTreatments", $script);
        $this->assertStringContainsString('function confirmClearanceTransition(id,status)', $script);
        $this->assertStringContainsString("method:'PATCH',headers:apiHeaders(true)", $script);
        $this->assertStringContainsString('clearanceStatusUrlTemplate: @json($clearanceStatusUrlTemplate)', $view);
        $this->assertStringContainsString('canEditTreatments: @json($canEditTreatments)', $view);
        $this->assertStringContainsString("'middleware' => ['can:admin.treatment_reservations.edit', 'throttle:30,1']", $routes);
        $this->assertStringContainsString('.clearance-flow{display:grid', $styles);
        $this->assertStringContainsString('.clr-shell .pay-table tr{display:grid', $styles);
    }

    #[Test]
    public function checkin_exposes_state_aware_arrival_actions_and_a_responsive_flow(): void
    {
        $script = file_get_contents(public_path('modules/lead/central/app.js'));
        $styles = file_get_contents(public_path('modules/lead/central/styles.css'));
        $view = file_get_contents(base_path('modules/Lead/Resources/views/admin/central/index.blade.php'));

        $this->assertStringContainsString('function checkinPrimaryAction(c,compact=false)', $script);
        $this->assertStringContainsString("actions.includes('confirm_arrival') && boot.canConfirmCheckin", $script);
        $this->assertStringContainsString('function confirmCheckinArrival(id)', $script);
        $this->assertStringContainsString("method:'POST',headers:apiHeaders(true)", $script);
        $this->assertStringContainsString("['booked', t('checkin.tab_booked'), s.scheduled]", $script);
        $this->assertStringContainsString('checkinConfirmUrlTemplate: @json($checkinConfirmUrlTemplate)', $view);
        $this->assertStringContainsString('.checkin-flow{background:linear-gradient', $styles);
        $this->assertStringContainsString('.cin-shell .pay-table tr{display:grid', $styles);
    }

    #[Test]
    public function bulk_actions_update_and_soft_delete_only_selected_leads(): void
    {
        foreach (range(1, 3) as $index) {
            DB::table('leads')->insert([
                'name' => "Bulk {$index}",
                'phone' => "6019000{$index}",
                'status' => Lead::STATUS_NEW,
                'source' => 'manual',
            ]);
        }

        $service = app(LeadWorkspaceService::class);
        $updated = $service->bulkUpdate([1, 2], 'status', Lead::STATUS_FOLLOW_UP);
        $sourceUpdated = $service->bulkUpdate([1, 3], 'source', 'WhatsApp');
        $dateUpdated = $service->bulkUpdate([1], 'created_at', '2026-09-10');
        $deleted = $service->bulkDelete([2]);

        $this->assertSame(2, $updated);
        $this->assertSame(2, $sourceUpdated);
        $this->assertSame(1, $dateUpdated);
        $this->assertSame('2026-09-10', Lead::findOrFail(1)->created_at?->format('Y-m-d'));
        $this->assertSame(Lead::STATUS_FOLLOW_UP, Lead::findOrFail(1)->status);
        $this->assertNotNull(Lead::findOrFail(1)->last_followed_up_at);
        $this->assertSame(Lead::STATUS_NEW, Lead::findOrFail(3)->status);
        $this->assertSame('WhatsApp', Lead::findOrFail(1)->source);
        $this->assertSame('WhatsApp', Lead::findOrFail(3)->source);
        $this->assertSame(1, $deleted);
        $this->assertSoftDeleted('leads', ['id' => 2]);
    }

    #[Test]
    public function bulk_update_request_rejects_unapproved_fields(): void
    {
        DB::table('leads')->insert([
            'name' => 'Validated lead',
            'phone' => '60195550000',
            'status' => Lead::STATUS_NEW,
            'source' => 'manual',
        ]);

        $request = BulkUpdateLeadsRequest::create('/', 'PATCH', [
            'ids' => [1],
            'field' => 'phone',
            'value' => '60123456789',
        ]);
        $validator = Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('field', $validator->errors()->toArray());

        $validStatusRequest = BulkUpdateLeadsRequest::create('/', 'PATCH', [
            'ids' => [1],
            'field' => 'status',
            'value' => Lead::STATUS_FOLLOW_UP,
        ]);
        $validSourceRequest = BulkUpdateLeadsRequest::create('/', 'PATCH', [
            'ids' => [1],
            'field' => 'source',
            'value' => 'TikTok',
        ]);
        $clearAssignmentRequest = BulkUpdateLeadsRequest::create('/', 'PATCH', [
            'ids' => [1],
            'field' => 'beautician_id',
            'value' => null,
        ]);
        $validDateRequest = BulkUpdateLeadsRequest::create('/', 'PATCH', [
            'ids' => [1],
            'field' => 'created_at',
            'value' => now()->toDateString(),
        ]);
        $futureDateRequest = BulkUpdateLeadsRequest::create('/', 'PATCH', [
            'ids' => [1],
            'field' => 'created_at',
            'value' => now()->addDay()->toDateString(),
        ]);

        $this->assertFalse(Validator::make(
            $validStatusRequest->all(),
            $validStatusRequest->rules(),
        )->fails());
        $this->assertFalse(Validator::make(
            $validSourceRequest->all(),
            $validSourceRequest->rules(),
        )->fails());
        $this->assertFalse(Validator::make(
            $clearAssignmentRequest->all(),
            $clearAssignmentRequest->rules(),
        )->fails());
        $this->assertFalse(Validator::make(
            $validDateRequest->all(),
            $validDateRequest->rules(),
        )->fails());
        $this->assertTrue(Validator::make(
            $futureDateRequest->all(),
            $futureDateRequest->rules(),
        )->fails());
    }

    #[Test]
    public function lead_directory_exposes_permission_aware_bulk_controls(): void
    {
        $script = file_get_contents(public_path('modules/lead/central/app.js'));
        $view = file_get_contents(base_path('modules/Lead/Resources/views/admin/central/index.blade.php'));

        $this->assertStringContainsString('boot.canEditLead||boot.canDeleteLead', $script);
        $this->assertStringContainsString('data-lead-select', $script);
        $this->assertStringContainsString("let leadBulkAction = ''", $script);
        $this->assertStringContainsString('actionControl.value=leadBulkAction', $script);
        $this->assertStringContainsString('leadBulkValueOptions(leadBulkAction)', $script);
        $this->assertStringContainsString('leadBulkUpdateUrl: @json($leadBulkUpdateUrl)', $view);
        $this->assertStringContainsString('leadBulkDeleteUrl: @json($leadBulkDeleteUrl)', $view);
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
        $wallet->setAttribute('transactions_count', 7);
        $wallet->setRelation('user', $user)->setRelation('tier', null)->setRelation('transactions', collect());
        $payload = app(CentralWalletService::class)->toArray($wallet);
        $this->assertSame(1, $payload['stamp_active']);
        $this->assertSame(LoyaltyStampWallet::all()->filter(fn ($stamp) => $stamp->isActive())->count(), $payload['stamp_active']);
        $this->assertSame('MEM-', substr($payload['code'], 0, 4));
        $this->assertSame(7, $payload['activity_count']);
    }

    #[Test]
    public function loyalty_membership_workspace_exposes_lifecycle_context_and_responsive_member_actions(): void
    {
        $script = file_get_contents(public_path('modules/lead/central/app.js'));
        $styles = file_get_contents(public_path('modules/lead/central/styles.css'));
        $english = file_get_contents(base_path('modules/Lead/Resources/lang/en/central.php'));
        $malay = file_get_contents(base_path('modules/Lead/Resources/lang/ms/central.php'));

        $this->assertStringContainsString('class="clearance-flow loyalty-flow"', $script);
        $this->assertStringContainsString("t('wallet.step_enrolled')", $script);
        $this->assertStringContainsString('boot.canShowLoyaltyMember && boot.loyaltyMemberShowUrlTemplate', $script);
        $this->assertStringContainsString("t('wallet.activity_summary'", $script);
        $this->assertStringContainsString('class="lead-menu loyalty-action-menu"', $script);
        $this->assertStringContainsString("closeAllLeadMenus(); reviewWallet", $script);
        $this->assertStringNotContainsString('class="loyalty-row-actions"', $script);
        $this->assertStringContainsString('.loyalty-metric--members{', $styles);
        $this->assertStringContainsString('.loyalty-action-menu .lead-menu__panel{min-width:190px}', $styles);
        $this->assertStringContainsString('.wal-shell .pay-table tr{display:grid', $styles);
        $this->assertStringContainsString("'title' => 'Loyalty Membership'", $english);
        $this->assertStringContainsString("'title' => 'Keahlian Loyalty'", $malay);
    }
}
