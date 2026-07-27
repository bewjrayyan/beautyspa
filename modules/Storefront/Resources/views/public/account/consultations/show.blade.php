@extends('storefront::public.account.layout')

@section('title', trans('account::consultation.pages.submission'))

@section('account_breadcrumb')
    <li><a href="{{ route('account.consultations.index') }}">{{ trans('account::consultation.pages.title') }}</a></li>
    <li class="active">{{ trans('account::consultation.pages.submission') }}</li>
@endsection

@section('panel')
    @php
        $treatmentName = $consultationContext['treatment_name']
            ?: trans('account::consultation.treatment_not_available');
        $appointmentDate = filled($consultationContext['appointment_date'] ?? null)
            ? \Illuminate\Support\Carbon::parse($consultationContext['appointment_date'])
            : null;
        $appointmentTime = $consultationContext['appointment_time'] ?? null;
        $branchName = $consultationContext['branch_name'] ?? null;
        $beauticianName = $consultationContext['beautician_name'] ?? null;
        $questionsSnapshot = $submission->questions_snapshot ?: [];
        $conditionalVisibility = app(\Modules\Account\Services\ConsultationConditionEvaluator::class)
            ->visibilityMap($questionsSnapshot, $submission->answers ?: []);
        $visibleSectionKeys = [];
        $currentSectionKey = null;

        foreach ($questionsSnapshot as $snapshotQuestion) {
            if (($snapshotQuestion['type'] ?? null) === 'section') {
                $currentSectionKey = $snapshotQuestion['key'] ?? null;
                continue;
            }

            if (($conditionalVisibility[$snapshotQuestion['key'] ?? ''] ?? true) && $currentSectionKey) {
                $visibleSectionKeys[$currentSectionKey] = true;
            }
        }
    @endphp

    <div class="consultation-record">
        <header class="consultation-record__header">
            <div>
                <span class="consultation-form-header__badge"><i class="las la-shield-alt" aria-hidden="true"></i>{{ trans('account::consultation.medical.confidential') }}</span>
                <h1>{{ $submission->form_title }}</h1>
                <p>{{ $submission->form_intro }}</p>
                <small>{{ trans('account::consultation.submitted', ['date' => $submission->submitted_at?->format('d M Y, H:i')]) }}</small>
            </div>
            <a class="btn consultation-record__download" href="{{ route('account.consultations.download', $submission) }}"><i class="las la-file-pdf" aria-hidden="true"></i> {{ trans('account::consultation.download_pdf') }}</a>
        </header>

        <section class="consultation-record__summary" aria-label="{{ trans('account::consultation.record_summary') }}">
            <div class="consultation-record__summary-item consultation-record__summary-item--treatment">
                <i class="las la-spa" aria-hidden="true"></i>
                <span>{{ trans('account::consultation.purchased_treatment') }}</span>
                <strong>{{ $treatmentName }}</strong>
            </div>
            <div class="consultation-record__summary-item">
                <i class="las la-receipt" aria-hidden="true"></i>
                <span>{{ trans('account::consultation.pdf.order') }}</span>
                <strong>{{ $submission->order_id ? trans('account::consultation.order_reference', ['id' => $submission->order_id]) : '—' }}</strong>
            </div>
            <div class="consultation-record__summary-item">
                <i class="las la-calendar-check" aria-hidden="true"></i>
                <span>{{ trans('account::consultation.appointment_details') }}</span>
                <strong>
                    @if ($appointmentDate || $appointmentTime)
                        @if ($appointmentDate){{ $appointmentDate->format('d M Y') }}@endif
                        @if ($appointmentTime){{ $appointmentDate ? ' · ' : '' }}{{ $appointmentTime }}@endif
                    @else
                        {{ trans('account::consultation.appointment_pending') }}
                    @endif
                </strong>
            </div>
            @if ($branchName)
                <div class="consultation-record__summary-item">
                    <i class="las la-store" aria-hidden="true"></i>
                    <span>{{ trans('account::consultation.spa_branch') }}</span>
                    <strong>{{ $branchName }}</strong>
                </div>
            @endif
            @if ($beauticianName)
                <div class="consultation-record__summary-item">
                    <i class="las la-user-nurse" aria-hidden="true"></i>
                    <span>{{ trans('account::consultation.beautician_label') }}</span>
                    <strong>{{ $beauticianName }}</strong>
                </div>
            @endif
        </section>

        <div class="consultation-record__notice"><i class="las la-shield-alt" aria-hidden="true"></i> {{ trans('account::consultation.submitted_notice') }}</div>

        <section class="consultation-record__answers">
            <h2>{{ trans('account::consultation.answers') }}</h2>
            @php $questionNumber = 0; @endphp
            @foreach ($questionsSnapshot as $question)
                @php $questionLabel = \Modules\Account\Support\ConsultationQuestionLabel::parts($question); @endphp
                @if (($question['type'] ?? null) === 'section')
                    @unless($visibleSectionKeys[$question['key'] ?? ''] ?? false) @continue @endunless
                    <h3 class="consultation-record__section-heading consultation-bilingual-label">
                        <span>{{ $questionLabel['primary'] }}</span>
                        @if ($questionLabel['english'])<small lang="en">{{ $questionLabel['english'] }}</small>@endif
                    </h3>
                    @continue
                @endif

                @unless($conditionalVisibility[$question['key'] ?? ''] ?? true) @continue @endunless

                @php
                    $questionNumber++;
                    $answer = data_get($submission->answers, $question['key']);
                @endphp

                @if (($question['type'] ?? null) === 'body_map')
                    <dl class="consultation-answer consultation-answer--body-map">
                        <dt>
                            <span class="consultation-question-number">{{ $questionNumber }}</span>
                            <span class="consultation-bilingual-label">
                                <span>{{ $questionLabel['primary'] }}</span>
                                @if ($questionLabel['english'])<small lang="en">{{ $questionLabel['english'] }}</small>@endif
                            </span>
                        </dt>
                        <dd>
                            @include('storefront::public.account.consultations.partials.body-map-figure', [
                                'options' => $question['options'] ?? [],
                                'selectedAreas' => is_array($answer) ? $answer : [],
                            ])
                            <div class="consultation-record__body-selections">
                                <span class="consultation-record__body-selections-title">{{ trans('account::consultation.selected_body_areas') }}</span>
                                <div>
                                    @forelse ((array) $answer as $area)
                                        @php $areaNumber = array_search($area, $question['options'] ?? [], true); @endphp
                                        <span><b>{{ $areaNumber === false ? '•' : $areaNumber + 1 }}</b>{{ $area }}</span>
                                    @empty
                                        <span>—</span>
                                    @endforelse
                                </div>
                            </div>
                        </dd>
                    </dl>
                @else
                    <dl class="consultation-answer">
                        <dt>
                            <span class="consultation-question-number">{{ $questionNumber }}</span>
                            <span class="consultation-bilingual-label">
                                <span>{{ $questionLabel['primary'] }}</span>
                                @if ($questionLabel['english'])<small lang="en">{{ $questionLabel['english'] }}</small>@endif
                            </span>
                        </dt>
                        <dd>{{ \Modules\Account\Support\ConsultationAnswerPresenter::display($answer) }}</dd>
                    </dl>
                @endif
            @endforeach
        </section>

        <section class="consultation-record__closing">
            <div class="consultation-record__consent">
                <span><i class="las la-check-circle" aria-hidden="true"></i>{{ trans('account::consultation.pdf.consent_accepted') }}</span>
                <h2>{{ trans('account::consultation.consent_statement') }}</h2>
                <p>{{ $submission->consent_text }}</p>
                @if (count($submission->legal_documents_snapshot ?? []))
                    <h3>{{ trans('account::consultation.legal.accepted_documents') }}</h3>
                    <ul class="consultation-record__legal-documents">
                        @foreach ($submission->legal_documents_snapshot as $document)
                            <li>
                                <strong>{{ $document['title'] ?? $document['slug'] }}</strong>
                                <small>{{ trans('account::consultation.legal.version', ['version' => $document['version'] ?? '—']) }}</small>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="consultation-record__signature">
                <h2>{{ trans('account::consultation.signature') }}</h2>
                <img src="{{ $signatureDataUri }}" alt="{{ trans('account::consultation.signature') }}">
                <small>{{ trans('account::consultation.pdf.signed_by', ['name' => $submission->customer_name, 'date' => $submission->submitted_at?->format('d M Y, H:i')]) }}</small>
            </div>
        </section>
    </div>
@endsection
