<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Http\Requests\SubmitConsultationRequest;
use Modules\Account\Services\ConsultationCustomerAccess;
use Modules\Account\Services\ConsultationFormService;
use Modules\Account\Services\ConsultationPdfService;
use Modules\Account\Services\ConsultationSignatureStorage;
use Modules\Account\Services\ConsultationAuditLogger;
use Modules\Account\Services\ConsultationContextService;
use Modules\Account\Services\LegalDocumentService;
use Modules\Account\Services\SubmitConsultation;

class AccountConsultationController extends Controller
{
    public function __construct(
        private readonly ConsultationFormService $forms,
        private readonly ConsultationCustomerAccess $customerAccess,
        private readonly LegalDocumentService $legalDocuments
    ) {
    }

    public function index(Request $request)
    {
        return response()->view('storefront::public.account.consultations.index', [
            'pendingForms' => $this->forms->pendingFor($request->user()),
            'submissions' => $this->forms->historyFor($request->user()),
        ])->withHeaders($this->privateRecordHeaders());
    }

    public function show(Request $request, ConsultationSubmission $submission)
    {
        $this->customerAccess->claim($submission, $request->user());

        if ($submission->isCompleted()) {
            return redirect()->route('account.consultations.show_submission', $submission);
        }

        ConsultationSubmission::query()
            ->whereKey($submission->id)
            ->whereNull('opened_at')
            ->update(['opened_at' => now()]);
        $submission->opened_at ??= now();
        $submission->load(['treatmentBooking.product', 'beautician']);

        return response()->view('storefront::public.account.consultations.form', [
            'submission' => $submission,
            'legalDocuments' => $this->legalDocuments->documentsOrFail(),
        ])->withHeaders($this->privateRecordHeaders());
    }

    public function store(
        SubmitConsultationRequest $request,
        ConsultationSubmission $submission,
        SubmitConsultation $submitConsultation
    ): RedirectResponse {
        $this->customerAccess->claim($submission, $request->user());

        if ($submission->isCompleted()) {
            return redirect()->route('account.consultations.show_submission', $submission)
                ->withSuccess(trans('account::consultation.messages.already_completed'));
        }

        $submitted = $submitConsultation->execute(
            $submission,
            $request->answers(),
            (string) $request->validated('signature_data'),
            $request->ip(),
            $request->userAgent()
        );

        if (! $submitted) {
            return redirect()->route('account.consultations.show_submission', $submission)
                ->withSuccess(trans('account::consultation.messages.already_completed'));
        }

        return redirect()->route('account.consultations.show_submission', $submission)
            ->withSuccess(trans('account::consultation.messages.submitted'));
    }

    public function showSubmission(
        Request $request,
        ConsultationSubmission $submission,
        ConsultationSignatureStorage $signatures,
        ConsultationAuditLogger $audit,
        ConsultationContextService $context
    )
    {
        $this->customerAccess->claim($submission, $request->user());
        abort_unless($submission->isCompleted(), 404);

        $audit->record(
            $submission,
            $request->user(),
            'customer',
            ConsultationAuditLogger::VIEWED,
            $request
        );

        return response()->view('storefront::public.account.consultations.show', [
            'submission' => $submission,
            'consultationContext' => $context->forDisplay($submission),
            'signatureDataUri' => $signatures->dataUri($submission),
        ])->withHeaders($this->privateRecordHeaders());
    }

    public function download(
        Request $request,
        ConsultationSubmission $submission,
        ConsultationPdfService $pdf,
        ConsultationAuditLogger $audit
    ): Response {
        $this->customerAccess->claim($submission, $request->user());
        abort_unless($submission->isCompleted(), 404);

        $response = $pdf->download($submission);

        $audit->record(
            $submission,
            $request->user(),
            'customer',
            ConsultationAuditLogger::PDF_DOWNLOADED,
            $request
        );

        return $response;
    }

    /** @return array<string, string> */
    private function privateRecordHeaders(): array
    {
        return [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ];
    }
}
