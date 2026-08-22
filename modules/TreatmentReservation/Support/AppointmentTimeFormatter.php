<?php

namespace Modules\TreatmentReservation\Support;

use Illuminate\Support\Carbon;

final class AppointmentTimeFormatter
{
    public static function toDisplay(?string $time): string
    {
        if (! filled($time)) {
            return '';
        }

        $parsed = self::parseParts($time);

        if ($parsed === null) {
            return trim((string) $time);
        }

        return Carbon::createFromTime($parsed['hour'], $parsed['minute'])->format('g:i A');
    }


    public static function toStorage(?string $time): ?string
    {
        if (! filled($time)) {
            return null;
        }

        $display = self::toDisplay($time);

        return $display !== '' ? $display : trim((string) $time);
    }


    public static function to24Hour(?string $time): ?string
    {
        $parsed = self::parseParts($time);

        if ($parsed === null) {
            return null;
        }

        return sprintf('%02d:%02d', $parsed['hour'], $parsed['minute']);
    }


    public static function parse(?string $time): ?Carbon
    {
        $parsed = self::parseParts($time);

        if ($parsed === null) {
            return null;
        }

        return Carbon::createFromTime($parsed['hour'], $parsed['minute']);
    }


    /**
     * @return array{hour: int, minute: int}|null
     */
    private static function parseParts(?string $time): ?array
    {
        if (! filled($time)) {
            return null;
        }

        $trimmed = trim((string) $time);

        if (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $trimmed, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
            $meridiem = strtoupper($matches[3]);

            if ($minute > 59) {
                return null;
            }

            if ($hour > 12) {
                return ['hour' => $hour, 'minute' => $minute];
            }

            $hour = $hour % 12;

            if ($meridiem === 'PM') {
                $hour += 12;
            }

            return ['hour' => $hour, 'minute' => $minute];
        }

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $trimmed, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];

            if ($hour > 23 || $minute > 59) {
                return null;
            }

            return ['hour' => $hour, 'minute' => $minute];
        }

        try {
            $parsed = Carbon::parse($trimmed);

            return ['hour' => (int) $parsed->format('H'), 'minute' => (int) $parsed->format('i')];
        } catch (\Throwable) {
            return null;
        }
    }


    /**
     * @param  array<string, mixed>  $option
     * @return array<string, mixed>
     */
    public static function decorateSlotOption(array $option): array
    {
        if (isset($option['time'])) {
            $option['time_label'] = self::toDisplay((string) $option['time']);
        }

        return $option;
    }


    /**
     * @param  list<array<string, mixed>>  $options
     * @return list<array<string, mixed>>
     */
    public static function decorateSlotOptions(array $options): array
    {
        return array_map(fn (array $option) => self::decorateSlotOption($option), $options);
    }
}
