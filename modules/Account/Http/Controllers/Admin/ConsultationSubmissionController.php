<?php

namespace Modules\Account\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Services\ConsultationPdfService;

class ConsultationSubmissionController extends Controller
{
    public function download(ConsultationSubmission $submission, ConsultationPdfService $pdf): Response
    {
        abort_unless($submission->isCompleted(), 404);

        return $pdf->download($submission->load(['user', 'treatmentBooking.product', 'beautician', 'order']));
    }
}
