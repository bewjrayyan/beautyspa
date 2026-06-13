<?php

namespace Modules\Beautician\Support;

use Illuminate\Support\Facades\Schema;
use Modules\Beautician\Entities\Beautician;
use Modules\Beautician\Entities\BeauticianJobTitle;

class JobTitleOptions
{
    public static function forSelect(?string $current = null): array
    {
        static::syncMissingTitles($current);

        $options = [
            '' => trans('beautician::beauticians.form.job_title_choose'),
        ];

        foreach (static::titles() as $name) {
            $options[$name] = $name;
        }

        return $options;
    }


    public static function syncMissingTitles(?string $title = null): void
    {
        if (! Schema::hasTable('beautician_job_titles')) {
            return;
        }

        $names = collect();

        if ($title !== null && $title !== '') {
            $names->push($title);
        }

        Beautician::query()
            ->whereNotNull('job_title')
            ->where('job_title', '!=', '')
            ->distinct()
            ->pluck('job_title')
            ->each(fn (string $name) => $names->push($name));

        if ($names->isEmpty()) {
            return;
        }

        $now = now();

        foreach ($names->unique()->filter() as $name) {
            BeauticianJobTitle::query()->firstOrCreate(
                ['name' => $name],
                ['position' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        BeauticianJobTitle::clearCache();
    }


    /**
     * @return array<int, string>
     */
    public static function titles(): array
    {
        if (! Schema::hasTable('beautician_job_titles')) {
            return [];
        }

        return BeauticianJobTitle::activeOrdered()
            ->pluck('name')
            ->all();
    }


    /**
     * @return array<int, string>
     */
    public static function activeNames(): array
    {
        return static::titles();
    }
}
