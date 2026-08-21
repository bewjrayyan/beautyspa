<?php

namespace Modules\TreatmentReservation\Entities;

use Modules\SpaBranch\Entities\SpaBranch;
use Modules\Support\Eloquent\Model;

class SpaBranchWeeklyAvailability extends Model
{
    protected $fillable = [
        'spa_branch_id',
        'day_of_week',
        'is_open',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_open' => 'boolean',
    ];


    public function spaBranch()
    {
        return $this->belongsTo(SpaBranch::class, 'spa_branch_id');
    }


    public function slots()
    {
        return $this->hasMany(SpaBranchWeeklyAvailabilitySlot::class, 'spa_branch_weekly_availability_id')
            ->orderBy('start_time');
    }
}
