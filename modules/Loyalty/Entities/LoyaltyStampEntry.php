<?php

namespace Modules\Loyalty\Entities;

use Modules\Order\Entities\Order;
use Modules\Support\Eloquent\Model;

class LoyaltyStampEntry extends Model
{
    protected $table = 'loyalty_stamp_entries';

    protected $fillable = [
        'wallet_id',
        'order_id',
        'stamps_added',
    ];

    protected $casts = [
        'stamps_added' => 'integer',
    ];


    public function wallet()
    {
        return $this->belongsTo(LoyaltyStampWallet::class, 'wallet_id');
    }


    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
