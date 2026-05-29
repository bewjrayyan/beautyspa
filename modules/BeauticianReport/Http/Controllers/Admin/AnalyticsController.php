<?php

namespace Modules\BeauticianReport\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\BeauticianReport\Services\AnalyticsService;
use Modules\Support\Money;

class AnalyticsController
{
    public function overview(AnalyticsService $analytics)
    {
        $data = $analytics->overview();

        return response()->json([
            'total_treatment_sales' => $data['totalTreatmentSales']->amount(),
            'total_treatment_sales_formatted' => $data['totalTreatmentSales']->format(),
            'total_treatment_orders' => $data['totalTreatmentOrders'],
            'completed_treatment_orders' => $data['completedTreatmentOrders'],
            'today_appointments' => $data['todayAppointments'],
            'upcoming_appointments' => $data['upcomingAppointments'],
            'active_beauticians' => $data['activeBeauticians'],
        ]);
    }

    public function salesTrend(Request $request, AnalyticsService $analytics)
    {
        $days = min(90, max(7, (int) $request->query('days', 30)));
        $trend = $analytics->salesTrend($days);

        return response()->json([
            'labels' => $trend['labels'],
            'amounts' => $trend['amounts'],
            'orders' => $trend['orders'],
            'formatted' => array_map(
                fn ($amount) => Money::inDefaultCurrency($amount)->format(),
                $trend['amounts']
            ),
            'currency' => $trend['currency'],
        ]);
    }
}
