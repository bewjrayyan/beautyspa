<?php

namespace Modules\Checkout\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\Order\Entities\Order;

class OrderGoogleCalendarUrl
{
    public function forOrder(Order $order): ?string
    {
        if (! $order->appointment_date || ! $order->appointment_time) {
            return null;
        }

        $timezone = setting('default_timezone') ?: config('app.timezone', 'Asia/Kuala_Lumpur');

        $start = Carbon::parse(
            $order->appointment_date->format('Y-m-d') . ' ' . $order->appointment_time,
            $timezone
        );

        $end = (clone $start)->addHour();

        $treatmentNames = $order->products->pluck('name')->filter()->unique()->implode(', ');

        $title = $treatmentNames !== ''
            ? $treatmentNames
            : trans('storefront::order_complete.calendar_event_title');

        if ($order->beautician) {
            $title .= ' — ' . $order->beautician->name;
        }

        $details = collect([
            trans('storefront::order_complete.calendar_order', ['id' => $order->id]),
            $order->customer_email,
            $order->customer_phone,
        ])->filter()->implode("\n");

        $location = collect([
            setting('store_name'),
            setting('store_address_1'),
            setting('store_city'),
            setting('store_zip'),
        ])->filter()->implode(', ');

        $query = http_build_query([
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $start->utc()->format('Ymd\THis\Z') . '/' . $end->utc()->format('Ymd\THis\Z'),
            'details' => $details,
            'location' => Str::limit($location, 500, ''),
        ]);

        return 'https://calendar.google.com/calendar/render?' . $query;
    }
}
