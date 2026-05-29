<?php

namespace Modules\TreatmentReservation\Entities;

use Modules\Beautician\Entities\Beautician;
use Modules\Support\Eloquent\Model;

class BeauticianWorkingHour extends Model
{
    protected $fillable = [
        'beautician_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];


    public function beautician()
    {
        return $this->belongsTo(Beautician::class);
    }
}
