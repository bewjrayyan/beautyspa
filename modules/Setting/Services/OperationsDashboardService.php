<?php

namespace Modules\Setting\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Account\Services\PrivacyRetentionService;
use Throwable;

class OperationsDashboardService
{
    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        $queue = $this->queueMetrics();
        $scheduler = $this->schedulerMetrics();
        $logs = [
            'csp' => $this->logSummary(
                (string) config('logging.channels.security.path'),
                'CSP violation reported.'
            ),
            'slow_queries' => $this->logSummary(
                (string) config('logging.channels.single.path'),
                'Slow database query detected.'
            ),
        ];

        try {
            $retentionPreview = app(PrivacyRetentionService::class)->preview();
        } catch (Throwable) {
            $retentionPreview = null;
        }

        return [
            'queue' => $queue,
            'scheduler' => $scheduler,
            'logs' => $logs,
            'retention' => [
                'enabled' => (bool) config('operations.privacy.retention_enabled', false),
                'completed_days' => config('operations.privacy.completed_consultation_days'),
                'preview' => $retentionPreview,
            ],
            'healthy' => $queue['healthy'] && $scheduler['healthy'],
            'alert_count' => ($queue['healthy'] ? 0 : 1) + ($scheduler['healthy'] ? 0 : 1),
        ];
    }

    public function alertCount(): int
    {
        try {
            return Cache::remember('admin.operations.alert_count', now()->addMinute(), function (): int {
                $snapshot = $this->snapshot();

                return (int) $snapshot['alert_count'];
            });
        } catch (Throwable) {
            return 0;
        }
    }

    /** @return array<string, mixed> */
    private function queueMetrics(): array
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
            'oldest_minutes' => (int) config('operations.queue.max_oldest_pending_minutes', 10),
        ];

        return [
            'pending' => $pending,
            'failed' => $failed,
            'oldest_minutes' => $oldestMinutes,
            'stuck_onesender' => $stuckOneSender,
            'limits' => $limits,
            'healthy' => $pending <= $limits['pending']
                && $failed <= $limits['failed']
                && $oldestMinutes <= $limits['oldest_minutes']
                && $stuckOneSender === 0,
        ];
    }

    /** @return array{last_seen_at: mixed, age_seconds: ?int, healthy: bool} */
    private function schedulerMetrics(): array
    {
        $lastSeen = Schema::hasTable('operation_heartbeats')
            ? DB::table('operation_heartbeats')->where('name', 'scheduler')->value('last_seen_at')
            : null;
        $age = $lastSeen ? max(0, (int) now()->diffInSeconds($lastSeen, true)) : null;

        return [
            'last_seen_at' => $lastSeen,
            'age_seconds' => $age,
            'healthy' => $age !== null && $age <= 180,
        ];
    }

    /** @return array{exists: bool, size_bytes: int, updated_at: ?string, recent_matches: int} */
    private function logSummary(string $path, string $needle): array
    {
        if ($path === '' || ! is_file($path)) {
            return ['exists' => false, 'size_bytes' => 0, 'updated_at' => null, 'recent_matches' => 0];
        }

        $size = (int) (@filesize($path) ?: 0);
        $handle = fopen($path, 'rb');
        $contents = '';

        if ($handle !== false) {
            if ($size > 524288) {
                fseek($handle, -524288, SEEK_END);
            }

            $contents = stream_get_contents($handle) ?: '';
            fclose($handle);
        }

        return [
            'exists' => true,
            'size_bytes' => $size,
            'updated_at' => date('Y-m-d H:i:s', (int) (@filemtime($path) ?: time())),
            'recent_matches' => substr_count($contents, $needle),
        ];
    }
}
