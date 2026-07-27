<?php

namespace Modules\Account\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Account\Contracts\PdfRenderer;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Exceptions\ConsultationPdfException;
use Throwable;

class ConsultationPdfService
{
    private const LAYOUT_VERSION = '4';

    public function __construct(private readonly PdfRenderer $renderer)
    {
    }

    public function download(ConsultationSubmission $submission): Response
    {
        $pdf = $this->contents($submission);

        $name = 'consultation-' . Str::slug($submission->user?->full_name ?: 'customer')
            . '-' . $submission->id . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
            'Content-Length' => (string) strlen($pdf),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function warm(ConsultationSubmission $submission): void
    {
        $this->contents($submission);
    }

    private function contents(ConsultationSubmission $submission): string
    {
        $submission->loadMissing('user');
        $consultationContext = app(ConsultationContextService::class)->forDisplay($submission);

        $fingerprint = hash('sha256', implode('|', [
            self::LAYOUT_VERSION,
            $submission->id,
            $submission->template_version,
            $submission->submitted_at?->toISOString(),
            $submission->signature_hash ?: hash('sha256', (string) $submission->signature_data),
            app()->getLocale(),
        ]));
        $path = "consultations/submissions/{$submission->id}/document-{$fingerprint}.pdf";

        if (
            Storage::disk('private')->exists($path)
        ) {
            return Storage::disk('private')->get($path);
        }

        try {
            $pdf = $this->renderer->render(
                view('account::consultations.pdf', [
                    'submission' => $submission,
                    'consultationContext' => $consultationContext,
                    'signatureDataUri' => app(ConsultationSignatureStorage::class)->dataUri($submission),
                ])->render()
            );
        } catch (Throwable $exception) {
            Log::error('Consultation PDF generation failed.', [
                'submission_id' => $submission->id,
                'exception' => $exception,
            ]);

            throw new ConsultationPdfException(
                'Unable to generate the consultation PDF.',
                previous: $exception
            );
        }

        Storage::disk('private')->put($path, $pdf);

        ConsultationSubmission::query()->whereKey($submission->id)->update([
            'pdf_path' => $path,
            'pdf_hash' => $fingerprint,
        ]);

        $submission->forceFill(['pdf_path' => $path, 'pdf_hash' => $fingerprint]);

        return $pdf;
    }
}
