<?php

namespace Modules\TreatmentReservation\Entities;

use Modules\Support\Eloquent\Model;

class TreatmentPublicHoliday extends Model
{
    protected $table = 'treatment_public_holidays';

    protected $fillable = [
        'date',
        'name',
        'day_name',
        'state_codes',
        'is_subject_to_change',
        'color',
        'source',
    ];

    protected $casts = [
        'state_codes' => 'array',
        'date' => 'date',
        'is_subject_to_change' => 'boolean',
    ];
}

