<?php

namespace Modules\GoogleIntegration\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class GoogleCalendarService
{
    public function __construct(
        private readonly GoogleServiceAccountClient $client,
    ) {
    }


    public static function isEnabled(): bool
    {
        return GoogleServiceAccountClient::isConfigured()
            && (bool) setting('google_calendar_enabled', false)
            && trim((string) setting('google_calendar_id', '')) !== '';
    }


    public function createAppointmentEvent(Order $order): string
    {
        $response = $this->client->http()->post(
            "https://www.googleapis.com/calendar/v3/calendars/{$this->encodedCalendarId()}/events",
            $this->appointmentPayload($order)
        );

        if ($response->failed()) {
            throw new Exception(
                'Google Calendar event failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        return (string) $response->json('id');
    }


    public function updateAppointmentEvent(Order $order, string $eventId): void
    {
        $response = $this->client->http()->patch(
            "https://www.googleapis.com/calendar/v3/calendars/{$this->encodedCalendarId()}/events/" . rawurlencode($eventId),
            $this->appointmentPayload($order)
        );

        if ($response->failed()) {
            throw new Exception(
                'Google Calendar event update failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }
    }


    public function createTreatmentAppointmentEvent(Order $order, TreatmentBooking $booking): string
    {
        $response = $this->client->http()->post(
            "https://www.googleapis.com/calendar/v3/calendars/{$this->encodedCalendarId()}/events",
            $this->treatmentAppointmentPayload($order, $booking)
        );

        if ($response->failed()) {
            throw new Exception(
                'Google Calendar treatment event failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        return (string) $response->json('id');
    }


    public function updateTreatmentAppointmentEvent(Order $order, TreatmentBooking $booking, string $eventId): void
    {
        $response = $this->client->http()->patch(
            "https://www.googleapis.com/calendar/v3/calendars/{$this->encodedCalendarId()}/events/" . rawurlencode($eventId),
            $this->treatmentAppointmentPayload($order, $booking)
        );

        if ($response->failed()) {
            throw new Exception(
                'Google Calendar treatment event update failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }
    }


    public function deleteAppointmentEvent(string $eventId): void
    {
        $response = $this->client->http()->delete(
            "https://www.googleapis.com/calendar/v3/calendars/{$this->encodedCalendarId()}/events/" . rawurlencode($eventId)
        );

        if ($response->failed() && ! in_array($response->status(), [404, 410], true)) {
            throw new Exception(
                'Google Calendar event deletion failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }
    }


    public function eventExists(string $eventId): bool
    {
        $eventId = trim($eventId);

        if ($eventId === '') {
            return false;
        }

        $response = $this->client->http()->get(
            "https://www.googleapis.com/calendar/v3/calendars/{$this->encodedCalendarId()}/events/" . rawurlencode($eventId)
        );

        if (in_array($response->status(), [404, 410], true)) {
            return false;
        }

        if ($response->failed()) {
            throw new Exception(
                'Google Calendar event lookup failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        return true;
    }


    private function encodedCalendarId(): string
    {
        return rawurlencode(trim((string) setting('google_calendar_id', '')));
    }


    /**
     * @return array<string, mixed>
     */
    private function appointmentPayload(Order $order): array
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
        $treatments = $order->products
            ->map(fn (OrderProduct $product) => $product->nameWithSelections() . ' (x' . $product->qty . ')')
            ->implode(', ');

        return [
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
                ...$this->treatmentWorkLogLines($order),
            ])),
            'start' => [
                'dateTime' => $start->toIso8601String(),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $end->toIso8601String(),
                'timeZone' => $timezone,
            ],
        ];
    }


    /**
     * @return array<string, mixed>
     */
    private function treatmentAppointmentPayload(Order $order, TreatmentBooking $booking): array
    {
        $order->loadMissing('products');
        $booking->loadMissing(['product', 'beautician']);

        if (! $booking->appointment_date || ! $booking->appointment_time) {
            throw new Exception('Treatment booking has no appointment date or time for Google Calendar.');
        }

        $timezone = setting('default_timezone', config('app.timezone', 'Asia/Kuala_Lumpur'));
        $start = Carbon::parse(
            $booking->appointment_date->format('Y-m-d') . ' ' . $booking->appointment_time,
            $timezone
        );
        $end = (clone $start)->addMinutes($booking->resolveSlotDurationMinutes());
        $customerName = trim($order->customer_first_name . ' ' . $order->customer_last_name);
        $treatment = $booking->product?->name ?: 'Treatment';
        $beautician = $booking->beautician?->name ?? '—';

        return [
            'summary' => "{$treatment} — {$customerName}",
            'description' => implode("\n", array_filter([
                "Order: #{$order->id}",
                "Appointment: #{$booking->id}",
                "Customer: {$customerName}",
                "Email: {$order->customer_email}",
                "Phone: {$order->customer_phone}",
                "Beautician: {$beautician}",
                "Treatment: {$treatment}",
                $order->note ? "Order note: {$order->note}" : null,
                $booking->workLogProgressLabel()
                    ? "Work log: {$booking->workLogProgressLabel()} complete"
                    : null,
                filled($booking->beautician_notes)
                    ? 'Customer note: ' . Str::limit(trim((string) $booking->beautician_notes), 1000, '…')
                    : null,
            ])),
            'start' => [
                'dateTime' => $start->toIso8601String(),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $end->toIso8601String(),
                'timeZone' => $timezone,
            ],
        ];
    }


    /**
     * Only the customer-visible note and checklist count are added here.
     * The structured checklist itself is never serialized to Google Calendar.
     *
     * @return array<int, string>
     */
    private function treatmentWorkLogLines(Order $order): array
    {
        if (! is_module_enabled('TreatmentReservation')) {
            return [];
        }

        $bookings = TreatmentBooking::query()
            ->where('order_id', $order->id)
            ->with(['product', 'beautician'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->orderBy('id')
            ->get();

        if ($bookings->isEmpty()) {
            return [];
        }

        $lines = ['', 'Appointments:'];

        foreach ($bookings as $booking) {
            $schedule = collect([
                $booking->appointment_date?->format('Y-m-d'),
                $booking->appointment_time,
            ])->filter()->implode(' ');
            $treatment = $booking->product?->name ?: 'Treatment';
            $lines[] = "#{$booking->id}: {$treatment}" . ($schedule ? " — {$schedule}" : '');

            if ($booking->workLogProgressLabel()) {
                $lines[] = "  Work log: {$booking->workLogProgressLabel()} complete";
            }

            if ($booking->beautician_notes_at) {
                $lines[] = '  Recorded: ' . $booking->beautician_notes_at->format('Y-m-d H:i');
            }

            if (filled($booking->beautician_notes)) {
                $lines[] = '  Customer note: ' . Str::limit(trim((string) $booking->beautician_notes), 1000, '…');
            }
        }

        return $lines;
    }
}
