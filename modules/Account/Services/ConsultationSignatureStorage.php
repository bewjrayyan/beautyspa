<?php

namespace Modules\Account\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Account\Entities\ConsultationSubmission;
use RuntimeException;

class ConsultationSignatureStorage
{
    /** @return array{path: string, hash: string} */
    public function store(int $submissionId, string $dataUri): array
    {
        $prefix = 'data:image/png;base64,';

        if (! str_starts_with($dataUri, $prefix)) {
            throw new RuntimeException('The consultation signature is not a PNG data URI.');
        }

        $binary = base64_decode(substr($dataUri, strlen($prefix)), true);

        if ($binary === false) {
            throw new RuntimeException('The consultation signature could not be decoded.');
        }

        $path = "consultations/submissions/{$submissionId}/signature-" . Str::uuid() . '.png';
        Storage::disk('private')->put($path, $binary);

        return ['path' => $path, 'hash' => hash('sha256', $binary)];
    }

    public function delete(?string $path): void
    {
        if (filled($path)) {
            Storage::disk('private')->delete($path);
        }
    }

    public function dataUri(ConsultationSubmission $submission): string
    {
        if (filled($submission->signature_path)) {
            $binary = Storage::disk('private')->get($submission->signature_path);

            return 'data:image/png;base64,' . base64_encode($binary);
        }

        return (string) $submission->signature_data;
    }
}
