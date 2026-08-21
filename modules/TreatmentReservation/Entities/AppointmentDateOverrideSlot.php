<?php

namespace Modules\TreatmentReservation\Entities;

use Modules\Support\Eloquent\Model;

class AppointmentDateOverrideSlot extends Model
{
    protected $fillable = [
        'appointment_date_override_id',
        'start_time',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];


    public function override()
    {
        return $this->belongsTo(AppointmentDateOverride::class, 'appointment_date_override_id');
    }
}
