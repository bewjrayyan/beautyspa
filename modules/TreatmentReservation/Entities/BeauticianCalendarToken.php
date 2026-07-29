<?php

namespace Modules\TreatmentReservation\Entities;

use Illuminate\Database\Eloquent\Model;

class BeauticianCalendarToken extends Model
{
    protected $fillable = [
        'beautician_id',
        'token_hash',
        'token_ciphertext',
        'expires_at',
        'revoked_at',
        'last_used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];
}
