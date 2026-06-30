<?php

namespace Modules\SpaBranch\Entities;

use Illuminate\Support\Collection;
use Modules\Admin\Ui\AdminTable;
use Modules\Beautician\Entities\Beautician;
use Modules\Support\Eloquent\Model;

class SpaBranch extends Model
{
    protected $fillable = [
        'name',
        'code',
        'phone',
        'email',
        'address',
        'position',
        'is_active',
    ];

    protected $attributes = [
        'position' => 0,
        'is_active' => true,
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function setPositionAttribute($value): void
    {
        $this->attributes['position'] = ($value === '' || $value === null) ? 0 : (int) $value;
    }

    public static function activeForContact(): Collection
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();
    }

    public static function activeListForCheckout(): array
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (SpaBranch $branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
            ])
            ->values()
            ->all();
    }

    public static function namesForFilter(): Collection
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id');
    }

    public function beauticians()
    {
        return $this->belongsToMany(Beautician::class, 'beautician_spa_branch');
    }

    public function orders()
    {
        return $this->hasMany(\Modules\Order\Entities\Order::class, 'spa_branch_id');
    }

    public function table()
    {
        return new AdminTable($this->newQuery());
    }
}
