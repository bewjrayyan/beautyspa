<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\Order\Entities\Order;

class SalesAnalyticsController
{
    /**
     * Display a listing of the resource.
     *
     * @param Order $order
     *
     * @return Response
     */
    public function index(Order $order)
    {
        $payload = Cache::remember('admin.dashboard.sales_analytics', now()->addMinutes(5), function () use ($order) {
            return [
                'labels' => $this->previousDays(),
                'data' => $order->salesAnalytics(),
            ];
        });

        return response()->json($payload);
    }

    private function previousDays()
    {
        $previousDays = array();

        for ($i = 0; $i <= 6; $i++) {
            $weekDay = now()->subDays($i)->weekDay();

            array_unshift($previousDays, trans('admin::dashboard.sales_analytics.day_names')[$weekDay - 1 < 0 ? 6 : $weekDay - 1]);
        }

        return $previousDays;
    }
}
