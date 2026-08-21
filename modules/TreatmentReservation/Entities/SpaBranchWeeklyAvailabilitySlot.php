<?php

namespace Modules\TreatmentReservation\Entities;

use Modules\Support\Eloquent\Model;

class SpaBranchWeeklyAvailabilitySlot extends Model
{
    protected $fillable = [
        'spa_branch_weekly_availability_id',
        'start_time',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];


    public function weeklyAvailability()
    {
        return $this->belongsTo(SpaBranchWeeklyAvailability::class, 'spa_branch_weekly_availability_id');
    }
}
