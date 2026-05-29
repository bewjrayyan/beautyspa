<?php

namespace Modules\Loyalty\Entities;

use Modules\Support\Eloquent\Model;

class LoyaltyRedemptionHold extends Model
{
    protected $table = 'loyalty_redemption_holds';

    protected $fillable = [
        'user_id',
        'cart_id',
        'points',
        'discount_amount',
        'expires_at',
    ];

    protected $casts = [
        'discount_amount' => 'float',
        'expires_at' => 'datetime',
    ];
}
