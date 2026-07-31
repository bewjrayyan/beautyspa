<?php

namespace AestheticCart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class QueueHealthCommand extends Command
{
    protected $signature = 'queue:health {--json : Emit machine-readable JSON}';

    protected $description = 'Check queue backlog, age, failures, and stuck OneSender work without changing data';

    public function handle(): int
    {
        $pending = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
        $failed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
        $oldest = Schema::hasTable('jobs') ? DB::table('jobs')->min('created_at') : null;
        $oldestMinutes = $oldest ? max(0, (int) floor((time() - (int) $oldest) / 60)) : 0;
        $stuckOneSender = Schema::hasTable('onesender_outbound_queue')
            ? DB::table('onesender_outbound_queue')
                ->where('status', 'processing')
                ->where('processing_at', '<=', now()->subMinutes(10))
                ->count()
            : 0;

        $limits = [
            'pending' => (int) config('operations.queue.max_pending', 100),
            'failed' => (int) config('operations.queue.max_failed', 0),
            'oldest_pending_minutes' => (int) config('operations.queue.max_oldest_pending_minutes', 10),
        ];
        $metrics = compact('pending', 'failed', 'oldestMinutes', 'stuckOneSender');
        $healthy = $pending <= $limits['pending']
            && $failed <= $limits['failed']
            && $oldestMinutes <= $limits['oldest_pending_minutes']
            && $stuckOneSender === 0;
        $report = ['healthy' => $healthy, 'metrics' => $metrics, 'limits' => $limits];

        if (! $healthy) {
            Log::warning('Queue health thresholds exceeded.', $report);
        }

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->line($healthy ? '<info>Queue health: OK</info>' : '<error>Queue health: DEGRADED</error>');
            $this->table(['Metric', 'Value'], [
                ['pending', $pending],
                ['failed', $failed],
                ['oldest_pending_minutes', $oldestMinutes],
                ['stuck_onesender', $stuckOneSender],
            ]);
        }

        return $healthy ? self::SUCCESS : self::FAILURE;
    }
}
