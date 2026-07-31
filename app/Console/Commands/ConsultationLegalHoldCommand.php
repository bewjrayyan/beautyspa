<?php

namespace AestheticCart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConsultationLegalHoldCommand extends Command
{
    protected $signature = 'privacy:consultation-legal-hold
        {submission : Consultation submission ID}
        {--release : Release the existing legal hold}
        {--reason= : Required reason when placing a hold}
        {--actor= : Optional admin user ID for the audit record}';

    protected $description = 'Place or release a legal hold on a consultation submission';

    public function handle(): int
    {
        if (! Schema::hasColumn('consultation_submissions', 'legal_hold_at')) {
            $this->error('Run migrations before managing legal holds.');

            return self::FAILURE;
        }

        $id = (int) $this->argument('submission');
        $release = (bool) $this->option('release');
        $reason = trim((string) $this->option('reason'));

        if (! $release && $reason === '') {
            $this->error('--reason is required when placing a legal hold.');

            return self::FAILURE;
        }

        $updated = DB::table('consultation_submissions')->where('id', $id)->update([
            'legal_hold_at' => $release ? null : now(),
            'legal_hold_by' => $release || ! $this->option('actor') ? null : (int) $this->option('actor'),
            'legal_hold_reason' => $release ? null : mb_substr($reason, 0, 500),
            'updated_at' => now(),
        ]);

        if ($updated === 0 && ! DB::table('consultation_submissions')->where('id', $id)->exists()) {
            $this->error('Consultation submission not found.');

            return self::FAILURE;
        }

        $this->info($release ? 'Legal hold released.' : 'Legal hold placed.');

        return self::SUCCESS;
    }
}
