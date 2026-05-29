<?php

namespace Modules\GoogleIntegration\Services;

use Modules\Order\Entities\Order;

class CompletedOrderGoogleSync
{
    public function __construct(
        private readonly GoogleSheetsService $sheets,
        private readonly GoogleCalendarService $calendar,
    ) {
    }


    public function sync(Order $order): void
    {
        if (! GoogleServiceAccountClient::isConfigured()) {
            return;
        }

        if (GoogleSheetsService::isEnabled() && ! $order->google_sheets_synced_at) {
            $this->sheets->appendOrderRow($this->sheets->buildRow($order));

            $order->forceFill(['google_sheets_synced_at' => now()])->save();
        }

        if (
            GoogleCalendarService::isEnabled()
            && ! $order->google_calendar_event_id
            && $order->appointment_date
        ) {
            $eventId = $this->calendar->createAppointmentEvent($order);

            $order->forceFill(['google_calendar_event_id' => $eventId])->save();
        }
    }
}
