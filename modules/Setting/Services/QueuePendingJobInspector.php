<?php

namespace Modules\Setting\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueuePendingJobInspector
{
    private ?bool $schedulerHealthy = null;

    public function __construct(
        private readonly OperationsDashboardService $dashboard,
    ) {
    }

    /** @return array{key: string, detail: ?string, context: ?string, severity: string} */
    public function inspect(object $job): array
    {
        $now = time();
        $createdAt = (int) ($job->created_at ?? 0);
        $availableAt = (int) ($job->available_at ?? $createdAt);
        $attempts = (int) ($job->attempts ?? 0);
        $ageMinutes = $createdAt > 0 ? max(0, (int) floor(($now - $createdAt) / 60)) : 0;
        $context = $this->jobContext((string) ($job->payload ?? ''));

        if (($job->reserved_at ?? null) !== null) {
            return [
                'key' => 'processing',
                'detail' => null,
                'context' => $context,
                'severity' => 'info',
            ];
        }

        if ($availableAt > $now) {
            return [
                'key' => 'delayed',
                'detail' => date('Y-m-d H:i:s', $availableAt),
                'context' => $context,
                'severity' => 'muted',
            ];
        }

        $connection = (string) config('queue.default', 'sync');

        if ($connection === 'sync') {
            return [
                'key' => 'sync_driver',
                'detail' => null,
                'context' => $context,
                'severity' => 'warning',
            ];
        }

        $schedulerHealthy = $this->schedulerHealthy();

        if ($attempts === 0 && $ageMinutes >= 2) {
            return [
                'key' => $schedulerHealthy ? 'waiting_worker' : 'waiting_worker_scheduler_offline',
                'detail' => (string) $ageMinutes,
                'context' => $context,
                'severity' => 'danger',
            ];
        }

        if ($attempts > 0) {
            return [
                'key' => 'retry_wait',
                'detail' => (string) $attempts,
                'context' => $context,
                'severity' => 'warning',
            ];
        }

        return [
            'key' => 'queued',
            'detail' => null,
            'context' => $context,
            'severity' => 'muted',
        ];
    }

    public function queueConnection(): string
    {
        return (string) config('queue.default', 'sync');
    }

    public function queueWorkerLikelyRunning(): bool
    {
        if (! Schema::hasTable('jobs')) {
            return true;
        }

        $stale = DB::table('jobs')
            ->where('attempts', 0)
            ->whereNull('reserved_at')
            ->where('created_at', '<=', now()->subMinutes(5)->getTimestamp())
            ->exists();

        return ! $stale;
    }

    private function jobContext(string $payload): ?string
    {
        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return null;
        }

        $displayName = (string) ($decoded['displayName'] ?? $decoded['data']['commandName'] ?? '');
        $command = $decoded['data']['command'] ?? null;

        if (! is_string($command) || $command === '') {
            return null;
        }

        if (str_contains($displayName, 'SyncOrderToGoogleJob') && preg_match('/s:7:"orderId";i:(\d+);/', $command, $matches)) {
            return 'order:#'.$matches[1];
        }

        if (preg_match('/s:7:"orderId";i:(\d+);/', $command, $matches)) {
            return 'order:#'.$matches[1];
        }

        if (preg_match('/s:2:"id";i:(\d+);/', $command, $matches)) {
            return 'id:#'.$matches[1];
        }

        return null;
    }

    private function schedulerHealthy(): bool
    {
        if ($this->schedulerHealthy === null) {
            $this->schedulerHealthy = (bool) ($this->dashboard->snapshot()['scheduler']['healthy'] ?? false);
        }

        return $this->schedulerHealthy;
    }
}
