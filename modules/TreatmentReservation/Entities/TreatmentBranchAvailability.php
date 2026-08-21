<?php

namespace Modules\TreatmentReservation\Entities;

use Modules\Product\Entities\Product;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\Support\Eloquent\Model;

class TreatmentBranchAvailability extends Model
{
    protected $fillable = [
        'product_id',
        'spa_branch_id',
        'duration_minutes',
        'capacity_per_slot',
        'allow_tba',
        'is_bookable',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'capacity_per_slot' => 'integer',
        'allow_tba' => 'boolean',
        'is_bookable' => 'boolean',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function spaBranch()
    {
        return $this->belongsTo(SpaBranch::class, 'spa_branch_id');
    }


    public function weeklyDays()
    {
        return $this->hasMany(TreatmentBranchWeeklyAvailability::class, 'treatment_branch_availability_id')
            ->orderBy('day_of_week');
    }
}
