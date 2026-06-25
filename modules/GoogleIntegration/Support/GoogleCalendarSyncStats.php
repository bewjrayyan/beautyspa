<?php

namespace Modules\GoogleIntegration\Support;

use Modules\GoogleIntegration\Services\GoogleCalendarService;
use Modules\Order\Entities\Order;

class GoogleCalendarSyncStats
{
    /**
     * @return array{
     *   synced: int,
     *   pending: int,
     *   all_synced: bool,
     *   calendar_url: ?string
     * }
     */
    public static function snapshot(): array
    {
        if (! GoogleCalendarService::isEnabled()) {
            return [
                'synced' => 0,
                'pending' => 0,
                'all_synced' => false,
                'calendar_url' => null,
            ];
        }

        $synced = Order::query()
            ->whereNotNull('google_calendar_event_id')
            ->count();

        $pending = Order::query()
            ->where('status', Order::COMPLETED)
            ->whereNotNull('appointment_date')
            ->whereNull('google_calendar_event_id')
            ->count();

        return [
            'synced' => $synced,
            'pending' => $pending,
            'all_synced' => $pending === 0,
            'calendar_url' => GoogleCalendarUrl::browserUrl((string) setting('google_calendar_id', '')),
        ];
    }
}
