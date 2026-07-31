<?php

namespace AestheticCart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Account\Services\PrivacyRetentionService;
use Throwable;

class ApplyPrivacyRetentionCommand extends Command
{
    protected $signature = 'privacy:apply-retention
        {--execute : Permanently delete eligible records and private files}
        {--limit= : Maximum consultation records and access logs per run}
        {--json : Emit machine-readable JSON}';

    protected $description = 'Preview or apply approved privacy retention rules (dry-run by default)';

    public function handle(PrivacyRetentionService $retention): int
    {
        $execute = (bool) $this->option('execute');
        $limit = (int) ($this->option('limit') ?: config('operations.privacy.chunk_size', 100));
        $runId = null;

        try {
            if ($execute && Schema::hasTable('privacy_retention_runs')) {
                $runId = DB::table('privacy_retention_runs')->insertGetId([
                    'mode' => 'execute',
                    'status' => 'running',
                    'started_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $counts = $execute ? $retention->execute($limit) : $retention->preview();
            $report = ['mode' => $execute ? 'execute' : 'dry-run', 'limit' => $limit, 'counts' => $counts];

            if ($runId !== null) {
                DB::table('privacy_retention_runs')->where('id', $runId)->update([
                    'status' => 'completed',
                    'counts' => json_encode($counts),
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($this->option('json')) {
                $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } else {
                $this->info($execute ? 'Retention completed.' : 'Dry-run only; no data or files were changed.');
                $this->table(['Candidate', 'Count'], collect($counts)->map(fn ($count, $name) => [$name, $count]));
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            if ($runId !== null) {
                DB::table('privacy_retention_runs')->where('id', $runId)->update([
                    'status' => 'failed',
                    'error' => mb_substr($exception->getMessage(), 0, 1000),
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
