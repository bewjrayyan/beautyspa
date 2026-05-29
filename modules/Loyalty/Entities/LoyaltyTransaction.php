<?php

namespace Modules\Loyalty\Entities;

use Modules\Support\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    public $timestamps = false;

    protected $table = 'loyalty_transactions';

    protected $fillable = [
        'wallet_id',
        'type',
        'points',
        'balance_after',
        'reference_type',
        'reference_id',
        'description',
        'meta',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];


    public function wallet(): BelongsTo
    {
        return $this->belongsTo(LoyaltyWallet::class, 'wallet_id');
    }
}
