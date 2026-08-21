<?php

namespace Modules\TreatmentReservation\Entities;

use Modules\Support\Eloquent\Model;

class TreatmentBranchWeeklyAvailability extends Model
{
    protected $fillable = [
        'treatment_branch_availability_id',
        'day_of_week',
        'is_open',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_open' => 'boolean',
    ];


    public function treatmentBranchAvailability()
    {
        return $this->belongsTo(TreatmentBranchAvailability::class, 'treatment_branch_availability_id');
    }


    public function slots()
    {
        return $this->hasMany(TreatmentBranchWeeklyAvailabilitySlot::class, 'treatment_branch_weekly_availability_id')
            ->orderBy('start_time');
    }
}
