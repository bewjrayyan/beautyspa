<?php

namespace Modules\TreatmentReservation\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\BeauticianCalendarToken;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Support\CalendarTokenVerifier;

class BeauticianIcalFeedService
{
    public function __construct(private CalendarTokenVerifier $tokenVerifier) {}


    public function tokenFor(int $beauticianId): string
    {
        $record = BeauticianCalendarToken::query()->firstOrCreate(
            ['beautician_id' => $beauticianId],
            $this->newTokenAttributes()
        );

        if ($record->revoked_at || ($record->expires_at && $record->expires_at->isPast())) {
            $record = $this->rotate($beauticianId);
        }

        return Crypt::decryptString($record->token_ciphertext);
    }


    public function isValidToken(int $beauticianId, string $token): bool
    {
        $record = BeauticianCalendarToken::query()
            ->where('beautician_id', $beauticianId)
            ->whereNull('revoked_at')
            ->first();

        if (! $record || ! $this->tokenVerifier->matches($record, $token, now())) {
            return false;
        }

        if (! $record->last_used_at || $record->last_used_at->lt(now()->subDay())) {
            $record->forceFill(['last_used_at' => now()])->saveQuietly();
        }

        return true;
    }


    public function rotate(int $beauticianId): BeauticianCalendarToken
    {
        $record = BeauticianCalendarToken::query()->firstOrNew(['beautician_id' => $beauticianId]);
        $record->forceFill($this->newTokenAttributes())->save();

        return $record;
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
            ->where('appointment_date', '>=', today()->subMonths(1)->toDateString())
            ->where('appointment_date', '<=', today()->addMonths(6)->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//AestheticCart//Treatment Reservations//EN',
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
        $summary .= ' — Booking #' . $booking->id;

        $description = collect([
            $booking->category?->name,
            $booking->status,
            $booking->workLogProgressLabel()
                ? 'Work log: ' . $booking->workLogProgressLabel() . ' complete'
                : null,
            $booking->beautician_notes_at
                ? 'Work log recorded: ' . $booking->beautician_notes_at->format('Y-m-d H:i')
                : null,
            filled($booking->beautician_notes)
                ? 'Customer note: ' . Str::limit(trim((string) $booking->beautician_notes), 1000, '…')
                : null,
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


    /** @return array{token_hash: string, token_ciphertext: string, expires_at: \Illuminate\Support\Carbon, revoked_at: null, last_used_at: null} */
    private function newTokenAttributes(): array
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        return [
            'token_hash' => hash('sha256', $token),
            'token_ciphertext' => Crypt::encryptString($token),
            'expires_at' => now()->addYear(),
            'revoked_at' => null,
            'last_used_at' => null,
        ];
    }
}
