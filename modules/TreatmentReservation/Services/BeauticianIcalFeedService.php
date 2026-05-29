<?php

namespace Modules\TreatmentReservation\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class BeauticianIcalFeedService
{
    public function tokenFor(int $beauticianId): string
    {
        return substr(hash_hmac('sha256', (string) $beauticianId, config('app.key')), 0, 32);
    }


    public function isValidToken(int $beauticianId, string $token): bool
    {
        return hash_equals($this->tokenFor($beauticianId), $token);
    }


    public function feedUrl(Beautician $beautician): string
    {
        return route('treatment_reservations.calendar.feed', [
            'beautician' => $beautician->id,
            'token' => $this->tokenFor($beautician->id),
        ]);
    }


    public function webcalUrl(Beautician $beautician): string
    {
        return preg_replace('/^https?/', 'webcal', $this->feedUrl($beautician));
    }


    public function googleCalendarSubscribeUrl(Beautician $beautician): string
    {
        return 'https://calendar.google.com/calendar/render?cid=' . urlencode($this->webcalUrl($beautician));
    }


    public function outlookSubscribeUrl(Beautician $beautician): string
    {
        return 'https://outlook.live.com/calendar/0/addfromweb?url='
            . urlencode($this->feedUrl($beautician))
            . '&name='
            . urlencode($this->calendarName($beautician));
    }


    public function calendarName(Beautician $beautician): string
    {
        return trim("{$beautician->name} — " . setting('store_name'));
    }


    public function generate(Beautician $beautician): string
    {
        $timezone = setting('default_timezone') ?: config('app.timezone', 'Asia/Kuala_Lumpur');
        $bookings = TreatmentBooking::query()
            ->withTreatmentProduct()
            ->with(['product', 'category'])
            ->where('beautician_id', $beautician->id)
            ->whereNotNull('appointment_date')
            ->whereNot('status', TreatmentBooking::STATUS_CANCELED)
            ->whereDate('appointment_date', '>=', today()->subMonths(1))
            ->whereDate('appointment_date', '<=', today()->addMonths(6))
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//FleetCart//Treatment Reservations//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . $this->escape("{$beautician->name} — " . setting('store_name')),
            'X-WR-TIMEZONE:' . $timezone,
        ];

        foreach ($bookings as $booking) {
            $event = $this->eventForBooking($booking, $timezone);

            if ($event) {
                $lines = array_merge($lines, $event);
            }
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines) . "\r\n";
    }


    /**
     * @return array<int, string>|null
     */
    private function eventForBooking(TreatmentBooking $booking, string $timezone): ?array
    {
        if (! $booking->appointment_date || ! $booking->appointment_time) {
            return null;
        }

        try {
            $start = Carbon::parse(
                $booking->appointment_date->format('Y-m-d') . ' ' . $booking->appointment_time,
                $timezone
            );
        } catch (\Throwable) {
            return null;
        }

        $end = (clone $start)->addHour();
        $uid = 'treatment-booking-' . $booking->id . '@' . parse_url(config('app.url'), PHP_URL_HOST);
        $summary = $booking->product?->name ?: 'Treatment';
        $summary .= ' — ' . $booking->customer_full_name;

        $description = collect([
            $booking->customer_phone,
            $booking->category?->name,
            $booking->status,
        ])->filter()->implode("\n");

        return [
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . $start->utc()->format('Ymd\THis\Z'),
            'DTEND:' . $end->utc()->format('Ymd\THis\Z'),
            'SUMMARY:' . $this->escape($summary),
            'DESCRIPTION:' . $this->escape($description),
            'STATUS:' . ($booking->status === TreatmentBooking::STATUS_CANCELED ? 'CANCELLED' : 'CONFIRMED'),
            'END:VEVENT',
        ];
    }


    private function escape(string $value): string
    {
        return Str::replace(["\r", "\n", ',', ';'], ['', '\\n', '\\,', '\\;'], $value);
    }
}
