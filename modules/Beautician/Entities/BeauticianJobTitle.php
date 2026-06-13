<?php

namespace Modules\Beautician\Entities;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Beautician\Admin\BeauticianJobTitleTable;
use Modules\Support\Eloquent\Model;

class BeauticianJobTitle extends Model
{
    protected $table = 'beautician_job_titles';

    protected $fillable = [
        'name',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }


    public static function clearCache(): void
    {
        Cache::forget(static::cacheKey());
    }


    public static function cacheKey(): string
    {
        return 'beautician_job_titles.active_ordered';
    }


    /**
     * @return Collection<int, self>
     */
    public static function activeOrdered(): Collection
    {
        return Cache::rememberForever(static::cacheKey(), function () {
            return static::query()
                ->where('is_active', true)
                ->orderBy('position')
                ->orderBy('name')
                ->get();
        });
    }


    public function isInUse(): bool
    {
        return Beautician::query()
            ->where('job_title', $this->name)
            ->exists();
    }


    public function table(): BeauticianJobTitleTable
    {
        return new BeauticianJobTitleTable($this->newQuery());
    }
}
