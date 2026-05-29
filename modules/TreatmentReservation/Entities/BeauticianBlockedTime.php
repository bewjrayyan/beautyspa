<?php

namespace Modules\TreatmentReservation\Entities;

use Modules\Beautician\Entities\Beautician;
use Modules\Support\Eloquent\Model;

class BeauticianBlockedTime extends Model
{
    protected $fillable = [
        'beautician_id',
        'block_date',
        'start_time',
        'end_time',
        'note',
    ];

    protected $casts = [
        'block_date' => 'date',
    ];


    public function beautician()
    {
        return $this->belongsTo(Beautician::class);
    }
}
