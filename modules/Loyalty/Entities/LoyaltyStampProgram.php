<?php

namespace Modules\Loyalty\Entities;

use Modules\Support\Eloquent\Model;

class LoyaltyStampProgram extends Model
{
    protected $table = 'loyalty_stamp_programs';

    protected $fillable = [
        'name',
        'reward_description',
        'stamps_required',
        'validity_days',
        'virtual_treatments_only',
        'product_ids',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'stamps_required' => 'integer',
        'validity_days' => 'integer',
        'virtual_treatments_only' => 'boolean',
        'product_ids' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];


    public function wallets()
    {
        return $this->hasMany(LoyaltyStampWallet::class, 'program_id');
    }


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
