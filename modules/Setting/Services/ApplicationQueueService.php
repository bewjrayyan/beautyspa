<?php

namespace Modules\Setting\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApplicationQueueService
{
    /** @return array{processed: int, remaining: int, error: ?string} */
    public function processPendingBatch(?string $connection = null, int $maxTime = 55, int $maxJobs = 100): array
    {
        $connection = $connection ?? (string) config('queue.default', 'sync');

        if ($connection === 'sync') {
            return ['processed' => 0, 'remaining' => 0, 'error' => 'sync'];
        }

        if (! Schema::hasTable('jobs')) {
            return ['processed' => 0, 'remaining' => 0, 'error' => 'no_table'];
        }

        $before = (int) DB::table('jobs')->count();

        if ($before === 0) {
            return ['processed' => 0, 'remaining' => 0, 'error' => null];
        }

        @set_time_limit(max(65, $maxTime + 15));

        $exitCode = Artisan::call('queue:work', [
            'connection' => $connection,
            '--stop-when-empty' => true,
            '--max-time' => $maxTime,
            '--max-jobs' => $maxJobs,
            '--sleep' => 1,
            '--tries' => 3,
            '--timeout' => 120,
        ]);

        $after = (int) DB::table('jobs')->count();

        if ($exitCode !== 0) {
            return [
                'processed' => max(0, $before - $after),
                'remaining' => $after,
                'error' => 'worker_failed',
            ];
        }

        return [
            'processed' => max(0, $before - $after),
            'remaining' => $after,
            'error' => null,
        ];
    }

    public function workerCommand(?string $connection = null): string
    {
        $connection = $connection ?? (string) config('queue.default', 'sync');

        return "php artisan queue:work {$connection} --sleep=3 --tries=3 --timeout=120";
    }

    public function cronScheduleCommand(): string
    {
        return '* * * * * cd '.base_path().' && php artisan schedule:run >> /dev/null 2>&1';
    }

    public function cronWorkerCommand(?string $connection = null): string
    {
        $connection = $connection ?? (string) config('queue.default', 'sync');

        return "* * * * * cd ".base_path()." && php artisan queue:work {$connection} --stop-when-empty --max-time=55 --tries=3 >> /dev/null 2>&1";
    }
}
