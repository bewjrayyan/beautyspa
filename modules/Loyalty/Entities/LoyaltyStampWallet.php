<?php

namespace Modules\Loyalty\Entities;

use Modules\Support\Eloquent\Model;
use Modules\User\Entities\User;

class LoyaltyStampWallet extends Model
{
    protected $table = 'loyalty_stamp_wallets';

    protected $fillable = [
        'user_id',
        'program_id',
        'stamps_count',
        'started_at',
        'expires_at',
        'completed_at',
        'redeemed_at',
        'redemption_code',
        'fulfilled_at',
        'fulfilled_by',
    ];

    protected $casts = [
        'stamps_count' => 'integer',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'fulfilled_by' => 'integer',
    ];


    public function program()
    {
        return $this->belongsTo(LoyaltyStampProgram::class, 'program_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function entries()
    {
        return $this->hasMany(LoyaltyStampEntry::class, 'wallet_id');
    }


    public function isActive(): bool
    {
        if ($this->completed_at || $this->redeemed_at) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }


    public function daysUntilExpiry(): ?int
    {
        if (! $this->expires_at || $this->expires_at->isPast()) {
            return null;
        }

        return max(0, (int) now()->diffInDays($this->expires_at, false));
    }
}
