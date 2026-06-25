<?php

namespace Modules\GoogleIntegration\Support;

class GoogleCalendarUrl
{
    public static function browserUrl(?string $calendarId): ?string
    {
        $calendarId = trim((string) $calendarId);

        if ($calendarId === '') {
            return null;
        }

        return 'https://calendar.google.com/calendar/u/0/r?cid=' . base64_encode($calendarId);
    }
}
