<?php

namespace Modules\Account\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PrivacyRetentionService
{
    /**
     * @return array{completed_consultations: int, expired_requests: int, access_logs: int, private_directories: int}
     */
    public function preview(): array
    {
        return [
            'completed_consultations' => $this->completedCandidates()->count(),
            'expired_requests' => $this->expiredRequestCandidates()->count(),
            'access_logs' => $this->accessLogCandidates()->count(),
            'private_directories' => 0,
        ];
    }

    /**
     * @return array{completed_consultations: int, expired_requests: int, access_logs: int, private_directories: int}
     */
    public function execute(int $limit): array
    {
        if (! config('operations.privacy.retention_enabled', false)) {
            throw new RuntimeException('Retention execution is disabled. Set PRIVACY_RETENTION_ENABLED=true after policy approval.');
        }

        $limit = max(1, min($limit, 5000));
        $counts = [
            'completed_consultations' => 0,
            'expired_requests' => 0,
            'access_logs' => 0,
            'private_directories' => 0,
        ];

        $completedIds = $this->completedCandidates()->orderBy('id')->limit($limit)->pluck('id');
        $remaining = max(0, $limit - $completedIds->count());
        $expiredIds = $this->expiredRequestCandidates()->orderBy('id')->limit($remaining)->pluck('id');

        foreach ($completedIds->concat($expiredIds) as $submissionId) {
            $deletedDirectory = false;
            $deleted = DB::transaction(function () use ($submissionId, &$deletedDirectory): bool {
                $candidate = DB::table('consultation_submissions')
                    ->where('id', $submissionId)
                    ->lockForUpdate()
                    ->first(['id', 'legal_hold_at']);

                if ($candidate === null || $candidate->legal_hold_at !== null) {
                    return false;
                }

                $directory = 'consultations/submissions/'.(int) $submissionId;

                if (Storage::disk('private')->exists($directory)) {
                    if (! Storage::disk('private')->deleteDirectory($directory)) {
                        throw new RuntimeException("Unable to delete private consultation directory for submission {$submissionId}.");
                    }

                    $deletedDirectory = true;
                }

                DB::table('consultation_access_logs')
                    ->where('consultation_submission_id', $submissionId)
                    ->delete();

                return DB::table('consultation_submissions')
                    ->where('id', $submissionId)
                    ->whereNull('legal_hold_at')
                    ->delete() === 1;
            });

            if (! $deleted) {
                continue;
            }

            $counts['private_directories'] += $deletedDirectory ? 1 : 0;
            $completedIds->contains($submissionId)
                ? $counts['completed_consultations']++
                : $counts['expired_requests']++;
        }

        $counts['access_logs'] = $this->accessLogCandidates()->limit($limit)->delete();

        return $counts;
    }

    private function completedCandidates(): Builder
    {
        $days = $this->positiveDays(config('operations.privacy.completed_consultation_days'));
        $query = DB::table('consultation_submissions')->whereRaw('1 = 0');

        if ($days === null || ! Schema::hasColumn('consultation_submissions', 'legal_hold_at')) {
            return $query;
        }

        return DB::table('consultation_submissions')
            ->whereNull('legal_hold_at')
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '<=', now()->subDays($days));
    }

    private function expiredRequestCandidates(): Builder
    {
        $days = $this->positiveDays(config('operations.privacy.expired_request_days'));
        $query = DB::table('consultation_submissions')->whereRaw('1 = 0');

        if ($days === null || ! Schema::hasColumn('consultation_submissions', 'legal_hold_at')) {
            return $query;
        }

        return DB::table('consultation_submissions')
            ->whereNull('legal_hold_at')
            ->whereNull('submitted_at')
            ->whereNotNull('public_token_expires_at')
            ->where('public_token_expires_at', '<=', now()->subDays($days));
    }

    private function accessLogCandidates(): Builder
    {
        $days = $this->positiveDays(config('operations.privacy.consultation_access_log_days'));
        $query = DB::table('consultation_access_logs')->whereRaw('1 = 0');

        if ($days === null
            || ! Schema::hasTable('consultation_access_logs')
            || ! Schema::hasColumn('consultation_submissions', 'legal_hold_at')) {
            return $query;
        }

        return DB::table('consultation_access_logs as logs')
            ->where('logs.created_at', '<=', now()->subDays($days))
            ->whereNotExists(function (Builder $held): void {
                $held->selectRaw('1')
                    ->from('consultation_submissions as submissions')
                    ->whereColumn('submissions.id', 'logs.consultation_submission_id')
                    ->whereNotNull('submissions.legal_hold_at');
            });
    }

    private function positiveDays(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $days = (int) $value;

        return $days > 0 ? $days : null;
    }
}
