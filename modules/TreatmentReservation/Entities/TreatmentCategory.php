<?php

namespace Modules\TreatmentReservation\Entities;

use Illuminate\Support\Str;
use Modules\Support\Eloquent\Model;

class TreatmentCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color',
        'is_active',
        'position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    protected static function booted(): void
    {
        static::saving(function (TreatmentCategory $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }


    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('name');
    }


    /**
     * @return array<int, array{name: string, value: int, color: string}>
     */
    public static function listForSelect(): array
    {
        return static::active()
            ->ordered()
            ->get(['id', 'name', 'color'])
            ->map(fn (self $category) => [
                'name' => $category->name,
                'value' => $category->id,
                'color' => $category->color,
            ])
            ->values()
            ->all();
    }
}
