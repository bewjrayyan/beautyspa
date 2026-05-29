<?php

namespace Modules\Loyalty\Entities;

use Modules\Loyalty\Admin\MemberTable;
use Modules\User\Entities\User;
use Modules\Support\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyWallet extends Model
{
    protected $table = 'loyalty_wallets';

    protected $fillable = [
        'user_id',
        'tier_id',
        'balance',
        'lifetime_spend',
        'tier_assigned_at',
    ];

    protected $casts = [
        'lifetime_spend' => 'float',
        'tier_assigned_at' => 'datetime',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function tier(): BelongsTo
    {
        return $this->belongsTo(LoyaltyTier::class, 'tier_id');
    }


    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'wallet_id')->latest('created_at');
    }


    public function table()
    {
        return new MemberTable(
            $this->newQuery()->with(['user', 'tier'])
        );
    }
}
