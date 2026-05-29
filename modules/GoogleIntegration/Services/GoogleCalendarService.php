<?php

namespace Modules\GoogleIntegration\Services;

use Carbon\Carbon;
use Exception;
use Modules\Order\Entities\Order;

class GoogleCalendarService
{
    public function __construct(
        private readonly GoogleServiceAccountClient $client,
    ) {
    }


    public static function isEnabled(): bool
    {
        return GoogleServiceAccountClient::isConfigured()
            && (bool) setting('google_calendar_enabled', true)
            && trim((string) setting('google_calendar_id', '')) !== '';
    }


    public function createAppointmentEvent(Order $order): string
    {
        $order->loadMissing(['products', 'beautician']);

        if (! $order->appointment_date) {
            throw new Exception('Order has no appointment date for Google Calendar.');
        }

        $timezone = setting('default_timezone', config('app.timezone', 'Asia/Kuala_Lumpur'));
        $start = Carbon::parse(
            $order->appointment_date->format('Y-m-d') . ' ' . ($order->appointment_time ?: '09:00'),
            $timezone
        );
        $end = (clone $start)->addHour();

        $customerName = trim($order->customer_first_name . ' ' . $order->customer_last_name);
        $beautician = $order->beautician?->name ?? '—';
        $treatments = $order->products->pluck('name')->implode(', ');

        $calendarId = rawurlencode(trim((string) setting('google_calendar_id', '')));

        $response = $this->client->http()->post(
            "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events",
            [
                'summary' => "Treatment #{$order->id} — {$customerName}",
                'description' => implode("\n", array_filter([
                    "Order: #{$order->id}",
                    "Customer: {$customerName}",
                    "Email: {$order->customer_email}",
                    "Phone: {$order->customer_phone}",
                    "Beautician: {$beautician}",
                    "Treatment: {$treatments}",
                    'Total: ' . $order->total->convertToCurrentCurrency()->format(),
                    $order->note ? "Note: {$order->note}" : null,
                ])),
                'start' => [
                    'dateTime' => $start->toIso8601String(),
                    'timeZone' => $timezone,
                ],
                'end' => [
                    'dateTime' => $end->toIso8601String(),
                    'timeZone' => $timezone,
                ],
            ]
        );

        if ($response->failed()) {
            throw new Exception(
                'Google Calendar event failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        return (string) $response->json('id');
    }
}
