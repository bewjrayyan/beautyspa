<?php

namespace Tests\Unit\Report;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Modules\Report\CouponsReport;
use Modules\Report\CustomersOrderReport;
use Modules\Report\Http\Controllers\Admin\ReportController;
use Modules\Report\Report;
use Modules\Report\Services\ReportDashboardService;
use Modules\Report\Services\ReportExportMapper;
use Modules\Report\Services\ReportExportService;
use Modules\Report\Support\ReportSpreadsheetSanitizer;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ReportAuditRegressionTest extends TestCase
{
    #[Test]
    public function malformed_dates_are_rejected_before_report_queries_run(): void
    {
        $request = Request::create('/admin/reports', 'GET', [
            'type' => 'sales_report',
            'from' => 'not-a-date',
        ]);
        $method = new ReflectionMethod(ReportController::class, 'validateFilters');

        $this->expectException(ValidationException::class);

        $method->invoke(new ReportController(), $request);
    }

    #[Test]
    public function array_report_types_redirect_safely_instead_of_throwing_a_type_error(): void
    {
        $request = Request::create('/admin/reports', 'GET', [
            'type' => ['sales_report'],
        ]);

        $response = (new ReportController())->index($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('type=coupons_report', $response->getTargetUrl());
    }

    #[Test]
    public function customer_order_report_no_longer_applies_invalid_grouping(): void
    {
        $request = Request::create('/admin/reports', 'GET', [
            'type' => 'customers_order_report',
            'group' => 'months',
        ]);
        $this->app->instance('request', $request);

        $sql = (new CustomersOrderReport())->report($request)->toSql();

        $this->assertStringNotContainsString('group by', strtolower($sql));
    }

    #[Test]
    public function empty_coupon_codes_do_not_filter_the_report_to_empty_strings(): void
    {
        $request = Request::create('/admin/reports', 'GET', [
            'type' => 'coupons_report',
            'coupon_code' => '',
        ]);
        $this->app->instance('request', $request);

        $sql = (new CouponsReport())->report($request)->toSql();

        $this->assertStringNotContainsString('`code` = ?', $sql);
    }

    #[Test]
    public function dashboard_status_cards_are_mutually_exclusive(): void
    {
        $request = Request::create('/admin/reports', 'GET', [
            'type' => 'sales_report',
        ]);

        $dashboard = (new ReportDashboardService())->overview($request);

        $this->assertLessThanOrEqual(
            $dashboard['totalOrders'],
            $dashboard['completedOrders'] + $dashboard['pendingOrders']
        );
    }

    #[Test]
    public function dashboard_totals_respect_the_active_date_range(): void
    {
        $request = Request::create('/admin/reports', 'GET', [
            'type' => 'sales_report',
            'from' => '2999-01-01',
            'to' => '2999-01-02',
        ]);

        $dashboard = (new ReportDashboardService())->overview($request);

        $this->assertSame(0, $dashboard['totalOrders']);
        $this->assertSame(0.0, (float) $dashboard['totalSales']->amount());
    }

    #[Test]
    public function spreadsheet_cells_neutralize_formula_prefixes(): void
    {
        foreach (['=1+1', '+SUM(A1:A2)', '-2+3', '@IMPORTXML("x")', "\t=cmd"] as $value) {
            $this->assertStringStartsWith("'", ReportSpreadsheetSanitizer::sanitize($value));
        }

        $this->assertSame('Normal customer', ReportSpreadsheetSanitizer::sanitize('Normal customer'));
        $this->assertSame(125.50, ReportSpreadsheetSanitizer::sanitize(125.50));
    }

    #[Test]
    public function oversized_exports_fail_explicitly_instead_of_truncating(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(0);

        try {
            ReportExportMapper::build(new OversizedReportStub(), Request::create('/'), 'sales_report');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
            throw $exception;
        }
    }

    #[Test]
    public function xlsx_exports_accept_the_binary_file_response_returned_by_laravel_excel(): void
    {
        $request = Request::create('/admin/reports/export', 'GET', [
            'type' => 'coupons_report',
            'format' => 'xlsx',
        ]);

        $response = (new ReportExportService())->export(new EmptyReportStub(), $request, 'coupons_report', 'xlsx');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }
}

class EmptyReportStub extends Report
{
    public function report($request): EmptyQueryStub
    {
        return new EmptyQueryStub();
    }

    protected function query(): EmptyQueryStub
    {
        return new EmptyQueryStub();
    }

    protected function view(): string
    {
        return '';
    }
}

class EmptyQueryStub
{
    public function limit(int $limit): self
    {
        return $this;
    }

    public function get(): Collection
    {
        return collect();
    }
}

class OversizedReportStub extends Report
{
    public function report($request): OversizedQueryStub
    {
        return new OversizedQueryStub();
    }

    protected function query(): OversizedQueryStub
    {
        return new OversizedQueryStub();
    }

    protected function view(): string
    {
        return '';
    }
}

class OversizedQueryStub
{
    public function limit(int $limit): self
    {
        return $this;
    }

    public function get(): Collection
    {
        return collect(array_fill(0, ReportExportMapper::MAX_EXPORT_ROWS + 1, (object) []));
    }
}
