<?php

namespace Modules\Account\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\User\Entities\User;

class ConsultationAuditLogger
{
    public const VIEWED = 'viewed';

    public const PDF_DOWNLOADED = 'pdf_downloaded';

    public function record(
        ConsultationSubmission $submission,
        ?User $actor,
        string $actorType,
        string $action,
        Request $request
    ): void {
        DB::table('consultation_access_logs')->insert([
            'consultation_submission_id' => $submission->id,
            'user_id' => $actor?->id,
            'actor_type' => mb_substr($actorType, 0, 20),
            'action' => mb_substr($action, 0, 40),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }
}
