<?php

namespace Modules\Account\Services;

use Modules\Account\Entities\ConsultationSubmission;

class SubmitConsultation
{
    public function __construct(private readonly LegalDocumentService $legalDocuments)
    {
    }

    public function execute(
        ConsultationSubmission $submission,
        array $answers,
        string $signature,
        ?string $ipAddress,
        ?string $userAgent
    ): bool {
        $updated = ConsultationSubmission::query()
            ->whereKey($submission->id)
            ->whereNull('submitted_at')
            ->whereNull('revoked_at')
            ->update([
                'answers' => json_encode($answers, JSON_UNESCAPED_UNICODE),
                'signature_data' => $signature,
                'consent_accepted' => true,
                'legal_documents_snapshot' => json_encode(
                    $this->legalDocuments->snapshot(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ),
                'submitted_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => mb_substr((string) $userAgent, 0, 500),
                'updated_at' => now(),
            ]);

        return $updated === 1;
    }
}
