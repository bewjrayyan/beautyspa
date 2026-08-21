<?php

namespace Modules\TreatmentReservation\Entities;

use Modules\Support\Eloquent\Model;

class TreatmentBranchWeeklyAvailabilitySlot extends Model
{
    protected $fillable = [
        'treatment_branch_weekly_availability_id',
        'start_time',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];


    public function weeklyAvailability()
    {
        return $this->belongsTo(TreatmentBranchWeeklyAvailability::class, 'treatment_branch_weekly_availability_id');
    }
}
