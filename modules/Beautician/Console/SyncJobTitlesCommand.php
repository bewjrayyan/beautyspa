<?php

namespace Modules\Beautician\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\Beautician\Entities\BeauticianJobTitle;
use Modules\Beautician\Support\JobTitleOptions;

class SyncJobTitlesCommand extends Command
{
    protected $signature = 'beautician:sync-job-titles';

    protected $description = 'Seed and sync all beautician job titles into master data';

    public function handle(): int
    {
        if (! Schema::hasTable('beautician_job_titles')) {
            $this->error('Table beautician_job_titles does not exist. Run migrations first.');

            return self::FAILURE;
        }

        $defaults = trans('beautician::beauticians.job_titles', [], 'en');

        if (! is_array($defaults)) {
            $defaults = [];
        }

        $now = now();
        $created = 0;

        foreach (array_values($defaults) as $index => $name) {
            $jobTitle = BeauticianJobTitle::query()->firstOrCreate(
                ['name' => $name],
                ['position' => $index, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );

            if ($jobTitle->wasRecentlyCreated) {
                $created++;
            }
        }

        JobTitleOptions::syncMissingTitles();

        $total = BeauticianJobTitle::count();

        $this->info("Job titles synced. {$created} new, {$total} total in master data.");

        BeauticianJobTitle::query()
            ->orderBy('position')
            ->orderBy('name')
            ->get(['name', 'position', 'is_active'])
            ->each(function (BeauticianJobTitle $jobTitle) {
                $status = $jobTitle->is_active ? 'active' : 'inactive';
                $this->line(sprintf('  [%s] %s (%d)', $status, $jobTitle->name, $jobTitle->position));
            });

        return self::SUCCESS;
    }
}
