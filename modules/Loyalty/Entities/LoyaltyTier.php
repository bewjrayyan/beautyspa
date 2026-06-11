<?php

namespace Modules\Loyalty\Entities;

use Illuminate\Support\Facades\Lang;
use Modules\Loyalty\Admin\TierTable;
use Modules\Support\Eloquent\Model;

class LoyaltyTier extends Model
{
    protected $table = 'loyalty_tiers';

    protected $fillable = [
        'slug',
        'name',
        'min_lifetime_spend',
        'earn_multiplier',
        'benefits',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'min_lifetime_spend' => 'float',
        'earn_multiplier' => 'float',
        'benefits' => 'array',
        'is_active' => 'boolean',
    ];


    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }


    public static function defaultTier(): ?self
    {
        return static::findBySlug(config('fleetcart.modules.loyalty.config.default_tier_slug', 'silver'))
            ?? static::orderBy('sort_order')->first();
    }


    public function wallets()
    {
        return $this->hasMany(LoyaltyWallet::class, 'tier_id');
    }


    public function translatedName(): string
    {
        if (! $this->slug) {
            return $this->name ?: '—';
        }

        $key = 'loyalty::tiers.names.' . $this->slug;

        return Lang::has($key) ? trans($key) : ($this->name ?: '—');
    }


    public function slugThemeClass(): string
    {
        return in_array($this->slug, ['silver', 'gold', 'platinum'], true)
            ? $this->slug
            : 'default';
    }


    /**
     * @return array<int, string>
     */
    public function benefitLines(int $limit = 2): array
    {
        $benefits = is_array($this->benefits) ? $this->benefits : [];

        return array_values(array_slice(array_filter(array_map('trim', $benefits)), 0, $limit));
    }


    public function table()
    {
        return new TierTable(
            $this->newQuery()->withCount('wallets')
        );
    }
}
