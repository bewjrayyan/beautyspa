<?php

namespace Modules\Account\Services;

use Modules\Account\Casts\EncryptedArrayWithLegacyFallback;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Jobs\GenerateConsultationPdf;
use Throwable;

class SubmitConsultation
{
    public function __construct(
        private readonly LegalDocumentService $legalDocuments,
        private readonly ConsultationSignatureStorage $signatures
    ) {
    }

    public function execute(
        ConsultationSubmission $submission,
        array $answers,
        string $signature,
        ?string $ipAddress,
        ?string $userAgent
    ): bool {
        $storedSignature = $this->signatures->store($submission->id, $signature);

        try {
            $updated = ConsultationSubmission::query()
                ->whereKey($submission->id)
                ->whereNull('submitted_at')
                ->whereNull('revoked_at')
                ->update([
                    'answers' => EncryptedArrayWithLegacyFallback::encrypt($answers),
                    'signature_data' => null,
                    'signature_path' => $storedSignature['path'],
                    'signature_hash' => $storedSignature['hash'],
                    'pdf_path' => null,
                    'pdf_hash' => null,
                    'consent_accepted' => true,
                    'legal_documents_snapshot' => EncryptedArrayWithLegacyFallback::encrypt(
                        $this->legalDocuments->snapshot()
                    ),
                    'submitted_at' => now(),
                    'ip_address' => $ipAddress,
                    'user_agent' => mb_substr((string) $userAgent, 0, 500),
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            $this->signatures->delete($storedSignature['path']);

            throw $exception;
        }

        if ($updated !== 1) {
            $this->signatures->delete($storedSignature['path']);

            return false;
        }

        if (config('queue.default') !== 'sync') {
            GenerateConsultationPdf::dispatch($submission->id)->afterCommit();
        }

        return true;
    }
}
