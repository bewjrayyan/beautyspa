<?php

namespace Modules\Account\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Account\Contracts\PdfRenderer;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Exceptions\ConsultationPdfException;
use Throwable;

class ConsultationPdfService
{
    public function __construct(private readonly PdfRenderer $renderer)
    {
    }

    public function download(ConsultationSubmission $submission): Response
    {
        try {
            $pdf = $this->renderer->render(
                view('account::consultations.pdf', compact('submission'))->render()
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

        $name = 'consultation-' . Str::slug($submission->user?->full_name ?: 'customer')
            . '-' . $submission->id . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);
    }
}
