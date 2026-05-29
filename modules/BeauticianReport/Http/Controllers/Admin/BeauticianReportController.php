<?php

namespace Modules\BeauticianReport\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\BeauticianReport\AppointmentsReport;
use Modules\BeauticianReport\BeauticianSalesReport;
use Modules\BeauticianReport\Services\AnalyticsService;
use Modules\BeauticianReport\TreatmentSalesDetailReport;

class BeauticianReportController
{
    private array $reports = [
        'beautician_sales_report' => BeauticianSalesReport::class,
        'appointments_report' => AppointmentsReport::class,
        'treatment_sales_report' => TreatmentSalesDetailReport::class,
    ];

    public function index(Request $request, AnalyticsService $analytics)
    {
        $type = $request->query('type');

        if (!$type) {
            return view('beauticianreport::admin.dashboard.index', [
                'analytics' => $analytics->overview(),
                'salesByBeautician' => $analytics->salesByBeautician(),
            ]);
        }

        if (!$this->reportTypeExists($type)) {
            return redirect()->route('admin.beautician_reports.index', [
                'type' => 'beautician_sales_report',
            ]);
        }

        return $this->report($type)->render($request);
    }

    private function reportTypeExists(?string $type): bool
    {
        return $type && array_key_exists($type, $this->reports);
    }

    private function report(string $type)
    {
        return new $this->reports[$type];
    }
}
