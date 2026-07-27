<?php

namespace Modules\Account\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Services\ConsultationPdfService;

class GenerateConsultationPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 300];

    public function __construct(public readonly int $submissionId)
    {
    }

    public function handle(ConsultationPdfService $pdf): void
    {
        $submission = ConsultationSubmission::query()
            ->whereKey($this->submissionId)
            ->whereNotNull('submitted_at')
            ->whereNull('revoked_at')
            ->first();

        if ($submission) {
            $pdf->warm($submission);
        }
    }
}
