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
        return view('storefront::public.account.consultations.index', [
            'pendingForms' => $this->forms->pendingFor($request->user()),
            'submissions' => $this->forms->historyFor($request->user()),
        ]);
    }

    public function show(Request $request, ConsultationSubmission $submission)
    {
        $this->customerAccess->claim($submission, $request->user());

        if ($submission->isCompleted()) {
            return redirect()->route('account.consultations.show_submission', $submission);
        }

        $submission->forceFill(['opened_at' => $submission->opened_at ?: now()])->saveQuietly();
        $submission->load(['treatmentBooking.product', 'beautician']);

        return view('storefront::public.account.consultations.form', [
            'submission' => $submission,
            'legalDocuments' => $this->legalDocuments->documentsOrFail(),
        ]);
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

    public function showSubmission(Request $request, ConsultationSubmission $submission)
    {
        $this->customerAccess->claim($submission, $request->user());
        abort_unless($submission->isCompleted(), 404);

        return view('storefront::public.account.consultations.show', [
            'submission' => $submission->load([
                'treatmentBooking.product',
                'treatmentBooking.beautician.spaBranches',
                'beautician',
                'product',
                'order.spaBranch',
                'order.beautician',
                'orderProduct',
            ]),
        ]);
    }

    public function download(
        Request $request,
        ConsultationSubmission $submission,
        ConsultationPdfService $pdf
    ): Response {
        $this->customerAccess->claim($submission, $request->user());
        abort_unless($submission->isCompleted(), 404);

        return $pdf->download($submission->load(['user', 'treatmentBooking.product', 'beautician', 'order']));
    }
}
