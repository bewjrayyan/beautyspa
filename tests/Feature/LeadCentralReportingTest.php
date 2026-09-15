<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Lead\Services\CentralMetricsService;
use Modules\Lead\Services\CentralReportingService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadCentralReportingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.lead_report_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::setDefaultConnection('lead_report_test');
        Schema::create('beauticians', function (Blueprint $t): void {
            $t->id(); $t->string('first_name'); $t->string('last_name');
            $t->boolean('is_active')->default(true); $t->integer('position')->default(0);
        });
        Schema::create('spa_branches', function (Blueprint $t): void { $t->id(); $t->string('name'); });
        Schema::create('leads', function (Blueprint $t): void {
            $t->id(); $t->integer('beautician_id')->nullable(); $t->integer('spa_branch_id')->nullable();
            $t->boolean('is_duplicate')->default(false); $t->string('status'); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('orders', function (Blueprint $t): void {
            $t->id(); $t->integer('beautician_id')->nullable(); $t->integer('spa_branch_id')->nullable();
            $t->string('customer_phone')->nullable();
            $t->string('payment_status'); $t->decimal('total', 12, 2); $t->timestamps(); $t->softDeletes();
        });
        DB::table('beauticians')->insert([['id'=>1,'first_name'=>'Leads only','last_name'=>''],['id'=>2,'first_name'=>'Sales only','last_name'=>''],['id'=>3,'first_name'=>'No records','last_name'=>'']]);
        DB::table('spa_branches')->insert([['id'=>1,'name'=>'Branch one'],['id'=>2,'name'=>'Branch two']]);
    }

    protected function tearDown(): void
    {
        DB::purge('lead_report_test');
        parent::tearDown();
    }

    #[Test]
    public function beautician_report_includes_real_staff_only_without_counting_unpaid_sales(): void
    {
        foreach ([['beautician_id'=>1,'spa_branch_id'=>1,'status'=>'converted'],['beautician_id'=>1,'spa_branch_id'=>1,'status'=>'follow_up'],['beautician_id'=>null,'spa_branch_id'=>null,'status'=>'new']] as $row) {
            DB::table('leads')->insert($row+['created_at'=>'2026-09-09','is_duplicate'=>false]);
        }
        DB::table('leads')->insert(['beautician_id'=>1,'spa_branch_id'=>1,'status'=>'converted','created_at'=>'2026-09-09','is_duplicate'=>true]);
        foreach ([['payment_status'=>'paid','total'=>100],['payment_status'=>'paid','total'=>200],['payment_status'=>'pending','total'=>900]] as $row) {
            DB::table('orders')->insert($row+['beautician_id'=>2,'spa_branch_id'=>2,'created_at'=>'2026-09-09']);
        }
        $service=app(CentralReportingService::class);
        $from=Carbon::parse('2026-09-01'); $to=Carbon::parse('2026-09-30')->endOfDay();
        $report=$service->performance('beauticians',$from,$to,null);
        $rows=collect($report['data'])->keyBy('id');
        $this->assertCount(3,$rows);
        $this->assertSame(2,$rows[1]['leads']);
        $this->assertSame(50.0,$rows[1]['conversion']);
        $this->assertSame(300.0,$rows[2]['revenue']);
        $this->assertSame(150.0,$rows[2]['average_order']);
        $this->assertSame(0,$rows[3]['leads']);
        $this->assertFalse($rows->has(0));
        $this->assertSame(2,$report['meta']['summary']['leads']);
        $branch=$service->performance('branches',$from,$to,1);
        $this->assertCount(1,$branch['data']);
        $this->assertSame(2,$branch['meta']['summary']['leads']);
        $this->assertSame(0.0,$branch['meta']['summary']['revenue']);
        $this->assertSame(0,$service->performance('beauticians',Carbon::parse('2026-08-01'),Carbon::parse('2026-08-31'),null)['meta']['summary']['leads']);
    }

    #[Test]
    public function sales_attribution_shows_only_real_beautician_names(): void
    {
        DB::table('beauticians')->where('id', 3)->update(['is_active' => false]);
        DB::table('leads')->insert([
            ['beautician_id' => 1, 'spa_branch_id' => 1, 'status' => 'converted', 'is_duplicate' => false, 'created_at' => '2026-09-09'],
            ['beautician_id' => 3, 'spa_branch_id' => 1, 'status' => 'new', 'is_duplicate' => false, 'created_at' => '2026-09-09'],
            ['beautician_id' => null, 'spa_branch_id' => 1, 'status' => 'new', 'is_duplicate' => false, 'created_at' => '2026-09-09'],
            ['beautician_id' => 999, 'spa_branch_id' => 1, 'status' => 'new', 'is_duplicate' => false, 'created_at' => '2026-09-09'],
            ['beautician_id' => null, 'spa_branch_id' => 1, 'status' => 'new', 'is_duplicate' => true, 'created_at' => '2026-09-09'],
        ]);
        DB::table('orders')->insert([
            ['beautician_id' => 2, 'spa_branch_id' => 1, 'customer_phone' => '60111111111', 'payment_status' => 'paid', 'total' => 300, 'created_at' => '2026-09-09'],
            ['beautician_id' => null, 'spa_branch_id' => 1, 'customer_phone' => '60122222222', 'payment_status' => 'paid', 'total' => 50, 'created_at' => '2026-09-09'],
            ['beautician_id' => 999, 'spa_branch_id' => 1, 'customer_phone' => '60133333333', 'payment_status' => 'paid', 'total' => 75, 'created_at' => '2026-09-09'],
        ]);

        $method = new \ReflectionMethod(CentralMetricsService::class, 'beauticianRows');
        $rows = collect($method->invoke(
            app(CentralMetricsService::class),
            Carbon::parse('2026-09-01'),
            Carbon::parse('2026-09-30')->endOfDay(),
            null,
        ));
        $named = $rows->whereNotNull('id')->keyBy('id');
        $this->assertSame('Leads only', $named[1]['name']);
        $this->assertSame('Sales only', $named[2]['name']);
        $this->assertSame('No records', $named[3]['name']);
        $this->assertCount(3, $rows);
        $this->assertFalse($rows->contains(fn (array $row): bool => $row['id'] === null || $row['id'] === 999));
        $this->assertSame(2, $rows->sum('leads'));
        $this->assertSame(300.0, $rows->sum('sales'));

        $reportRows = collect(app(CentralReportingService::class)->performance(
            'beauticians',
            Carbon::parse('2026-09-01'),
            Carbon::parse('2026-09-30')->endOfDay(),
            null,
        )['data']);
        $this->assertCount(3, $reportRows);
        $this->assertFalse($reportRows->contains(fn (array $row): bool => $row['id'] === 0 || $row['id'] === 999));
    }

    #[Test]
    public function audit_pagination_and_summary_respect_period_branch_and_batch_search(): void
    {
        Schema::create('lead_imports', function (Blueprint $t): void {
            $t->id(); $t->string('batch_code'); $t->string('method'); $t->string('status'); $t->integer('spa_branch_id')->nullable(); $t->integer('uploaded_by')->nullable();
            foreach(['raw_count','imported_count','duplicate_count','invalid_count'] as $col) $t->integer($col)->default(0);
            $t->timestamps();
        });
        for($i=0;$i<27;$i++) DB::table('lead_imports')->insert(['batch_code'=>'IMP-'.$i,'method'=>'csv','status'=>'completed','spa_branch_id'=>1,'created_at'=>'2026-09-09','imported_count'=>2]);
        DB::table('lead_imports')->insert(['batch_code'=>'OLD','method'=>'csv','status'=>'completed','spa_branch_id'=>2,'created_at'=>'2026-08-09','imported_count'=>10]);
        $service=app(CentralReportingService::class);
        $from=Carbon::parse('2026-09-01'); $to=Carbon::parse('2026-09-30');
        $report=$service->audit($from,$to,1,'',2);
        $this->assertCount(2,$report['data']);
        $this->assertSame(27,$report['meta']['total']);
        $this->assertSame(54,$report['meta']['summary']['imported']);
        $this->assertSame(1,$service->audit($from,$to,1,'IMP-26')['meta']['total']);
        $this->assertSame(0,$service->audit($from,$to,2)['meta']['total']);
    }
}
