<?php

use Modules\TreatmentReservation\Support\AppointmentTimeFormatter;

if (! function_exists('appointment_time_display')) {
    function appointment_time_display(?string $time): string
    {
        return AppointmentTimeFormatter::toDisplay($time);
    }
}
