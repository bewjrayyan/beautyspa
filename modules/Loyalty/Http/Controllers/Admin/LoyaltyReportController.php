<?php

namespace Modules\Loyalty\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Loyalty\Services\LoyaltyReportService;

class LoyaltyReportController
{
    public function __construct(private LoyaltyReportService $reports) {}


    public function index(Request $request)
    {
        $overview = $this->reports->overview(
            $request->get('from'),
            $request->get('to')
        );

        return view('loyalty::admin.reports.index', [
            'overview' => $overview,
            'transactions' => $this->reports->recentTransactions(30),
            'request' => $request,
        ]);
    }
}
