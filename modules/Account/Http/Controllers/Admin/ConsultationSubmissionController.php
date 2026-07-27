<?php

namespace Modules\Account\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Services\ConsultationPdfService;
use Modules\Account\Services\ConsultationAuditLogger;

class ConsultationSubmissionController extends Controller
{
    public function download(
        Request $request,
        ConsultationSubmission $submission,
        ConsultationPdfService $pdf,
        ConsultationAuditLogger $audit
    ): Response
    {
        abort_unless($submission->isCompleted(), 404);
        $response = $pdf->download($submission);

        $audit->record(
            $submission,
            $request->user(),
            'admin',
            ConsultationAuditLogger::PDF_DOWNLOADED,
            $request
        );

        return $response;
    }
}
