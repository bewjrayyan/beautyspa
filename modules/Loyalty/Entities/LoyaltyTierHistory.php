<?php

namespace Modules\Loyalty\Entities;

use Modules\Support\Eloquent\Model;

class LoyaltyTierHistory extends Model
{
    public $timestamps = false;

    protected $table = 'loyalty_tier_history';

    protected $fillable = [
        'user_id',
        'from_tier_id',
        'to_tier_id',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
