<?php

namespace Modules\Setting\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Modules\Setting\Services\OperationsAuditLogger;
use Modules\Setting\Services\OperationsDashboardService;

class OperationsController
{
    public function index(Request $request, OperationsDashboardService $dashboard): View
    {
        $pendingJobs = Schema::hasTable('jobs')
            ? DB::table('jobs')
                ->select(['id', 'queue', 'payload', 'attempts', 'reserved_at', 'created_at'])
                ->orderByDesc('id')
                ->paginate(20, ['*'], 'pending_page')
            : null;

        if ($pendingJobs) {
            $pendingJobs->setCollection($pendingJobs->getCollection()->map(function (object $job): object {
                $job->display_name = $this->jobDisplayName((string) $job->payload);
                unset($job->payload);

                return $job;
            }));
        }

        $failedJobs = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')
                ->select(['id', 'uuid', 'connection', 'queue', 'payload', 'failed_at'])
                ->orderByDesc('id')
                ->paginate(20, ['*'], 'failed_page')
            : null;

        if ($failedJobs) {
            $failedJobs->setCollection($failedJobs->getCollection()->map(function (object $job): object {
                $job->display_name = $this->jobDisplayName((string) $job->payload);
                unset($job->payload);

                return $job;
            }));
        }

        $retentionRuns = Schema::hasTable('privacy_retention_runs')
            ? DB::table('privacy_retention_runs')
                ->select(['id', 'mode', 'status', 'counts', 'started_at', 'completed_at'])
                ->orderByDesc('id')
                ->limit(10)
                ->get()
            : collect();
        $legalHolds = Schema::hasColumn('consultation_submissions', 'legal_hold_at')
            ? DB::table('consultation_submissions')
                ->select(['id', 'legal_hold_at', 'legal_hold_by', 'legal_hold_reason'])
                ->whereNotNull('legal_hold_at')
                ->orderByDesc('legal_hold_at')
                ->limit(20)
                ->get()
            : collect();
        $audits = Schema::hasTable('admin_operation_audits')
            ? DB::table('admin_operation_audits')
                ->select(['id', 'user_id', 'action', 'target_type', 'target_id', 'created_at'])
                ->orderByDesc('id')
                ->limit(20)
                ->get()
            : collect();

        return view('setting::admin.operations.index', [
            'snapshot' => $dashboard->snapshot(),
            'pendingJobs' => $pendingJobs,
            'failedJobs' => $failedJobs,
            'retentionRuns' => $retentionRuns,
            'legalHolds' => $legalHolds,
            'audits' => $audits,
            'canManageQueue' => $request->user()?->hasAccess('admin.operations.manage_queue') ?? false,
            'canManageRetention' => $request->user()?->hasAccess('admin.operations.manage_retention') ?? false,
        ]);
    }

    public function cancelPending(
        Request $request,
        int $job,
        OperationsAuditLogger $audit
    ): RedirectResponse {
        $deleted = DB::transaction(function () use ($job): bool {
            $queued = DB::table('jobs')->where('id', $job)->lockForUpdate()->first();

            if ($queued === null || $queued->reserved_at !== null) {
                return false;
            }

            return DB::table('jobs')->where('id', $job)->whereNull('reserved_at')->delete() === 1;
        });

        if (! $deleted) {
            return back()->with('error', trans('setting::operations.queue_cancel_failed'));
        }

        $audit->record($request, 'queue.cancelled', 'job', $job);
        $this->forgetDashboardCache();

        return back()->with('success', trans('setting::operations.queue_cancelled'));
    }

    public function retryFailed(
        Request $request,
        string $uuid,
        OperationsAuditLogger $audit
    ): RedirectResponse {
        abort_unless(
            Schema::hasTable('failed_jobs') && DB::table('failed_jobs')->where('uuid', $uuid)->exists(),
            404
        );

        if (Artisan::call('queue:retry', ['id' => [$uuid]]) !== 0) {
            return back()->with('error', trans('setting::operations.queue_retry_failed'));
        }
        $audit->record($request, 'queue.retried', 'failed_job', $uuid);
        $this->forgetDashboardCache();

        return back()->with('success', trans('setting::operations.queue_retried'));
    }

    public function placeLegalHold(
        Request $request,
        int $submission,
        OperationsAuditLogger $audit
    ): RedirectResponse {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);
        $exists = DB::table('consultation_submissions')->where('id', $submission)->exists();
        abort_unless($exists, 404);

        DB::table('consultation_submissions')->where('id', $submission)->update([
            'legal_hold_at' => now(),
            'legal_hold_by' => $request->user()?->id,
            'legal_hold_reason' => $data['reason'],
            'updated_at' => now(),
        ]);

        $audit->record($request, 'retention.legal_hold_placed', 'consultation_submission', $submission);

        return back()->with('success', trans('setting::operations.legal_hold_placed'));
    }

    public function releaseLegalHold(
        Request $request,
        int $submission,
        OperationsAuditLogger $audit
    ): RedirectResponse {
        $record = DB::table('consultation_submissions')
            ->where('id', $submission)
            ->whereNotNull('legal_hold_at')
            ->first(['id', 'legal_hold_reason']);
        abort_unless($record !== null, 404);

        DB::table('consultation_submissions')->where('id', $submission)->update([
            'legal_hold_at' => null,
            'legal_hold_by' => null,
            'legal_hold_reason' => null,
            'updated_at' => now(),
        ]);

        $audit->record(
            $request,
            'retention.legal_hold_released',
            'consultation_submission',
            $submission,
            ['previous_reason_hash' => hash('sha256', (string) $record->legal_hold_reason)]
        );

        return back()->with('success', trans('setting::operations.legal_hold_released'));
    }

    private function jobDisplayName(string $payload): string
    {
        $decoded = json_decode($payload, true);
        $name = is_array($decoded)
            ? ($decoded['displayName'] ?? $decoded['data']['commandName'] ?? 'unknown')
            : 'unknown';

        return mb_substr(class_basename((string) $name), 0, 120);
    }

    private function forgetDashboardCache(): void
    {
        Cache::forget('admin.operations.alert_count');
    }
}
