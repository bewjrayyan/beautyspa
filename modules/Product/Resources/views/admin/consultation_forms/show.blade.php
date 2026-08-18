@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', $template->name)
    <li><a href="{{ route('admin.consultation_forms.index') }}">{{ trans('product::consultation_forms.title') }}</a></li>
    <li class="active">{{ trans('product::consultation_forms.preview') }}</li>
@endcomponent

@section('content')
    <div class="consultation-form-shell" style="max-width: 900px; margin: 0 auto;">
        <header class="consultation-form-header">
            <div class="consultation-form-header__badge"><i class="fa fa-heartbeat" aria-hidden="true"></i> {{ trans('account::consultation.medical.confidential') }}</div>
            <span class="consultation-preview-badge">{{ trans('product::consultation_forms.preview_mode') }}</span>
            <h1>{{ $template->title }}</h1>
            @if ($template->intro)<p>{{ $template->intro }}</p>@endif
            <small>{{ trans('account::consultation.form_help') }}</small>
        </header>

        <section class="consultation-medical-profile" aria-labelledby="consultation-customer-profile">
            <div class="consultation-medical-profile__icon"><i class="fa fa-user-md" aria-hidden="true"></i></div>
            <div class="consultation-medical-profile__content">
                <span>{{ trans('account::consultation.medical.patient_details') }}</span>
                <h2 id="consultation-customer-profile">{{ $mockSubmission->customer_name }}</h2>
                <div class="consultation-medical-profile__grid">
                    <div><i class="las la-envelope"></i><small>{{ trans('account::consultation.medical.email') }}</small><strong>{{ $mockSubmission->customer_email }}</strong></div>
                    <div><i class="las la-phone"></i><small>{{ trans('account::consultation.medical.phone') }}</small><strong>{{ $mockSubmission->customer_phone }}</strong></div>
                    <div><i class="las la-id-card"></i><small>{{ trans('account::consultation.medical.identity') }}</small><strong>900101-12-3456</strong></div>
                    <div><i class="las la-calendar"></i><small>{{ trans('account::consultation.medical.appointment') }}</small><strong>{{ now()->addDays(7)->format('d M Y') }}</strong></div>
                </div>
            </div>
        </section>

        <div class="consultation-form-preview" novalidate>
            @php
                $questionsSnapshot = $template->questions ?: [];
                $conditionalVisibility = app(\Modules\Account\Services\ConsultationConditionEvaluator::class)
                    ->visibilityMap($questionsSnapshot, []);
                $questionNumber = 0;
            @endphp
            @foreach ($questionsSnapshot as $index => $question)
                @php
                    $key = $question['key'];
                    $type = $question['type'] ?? 'text';
                    $required = !empty($question['required']);
                    $questionLabel = \Modules\Account\Support\ConsultationQuestionLabel::parts($question);
                    $isVisible = $conditionalVisibility[$key] ?? true;
                @endphp

                @if ($type === 'section')
                    <div class="consultation-medical-section">
                        <span class="consultation-medical-section__icon" aria-hidden="true">
                            <i class="fa {{ str_contains($key, 'body') ? 'fa-map-marker' : (str_contains($key, 'goal') ? 'fa-bullseye' : 'fa-stethoscope') }}"></i>
                        </span>
                        <div>
                            <small>{{ trans('account::consultation.medical.section') }}</small>
                            <h2 class="consultation-bilingual-label">
                                <span>{{ $questionLabel['primary'] }}</span>
                                @if ($questionLabel['english'])<small lang="en">{{ $questionLabel['english'] }}</small>@endif
                            </h2>
                        </div>
                    </div>
                    @continue
                @endif

                @php
                    $questionNumber++;
                @endphp
                <fieldset
                    class="consultation-question consultation-question--{{ str_replace('_', '-', $type) }} @if(data_get($question, 'condition.enabled', false)) consultation-question--followup @endif @unless($isVisible) hidden @endunless"
                    data-question-key="{{ $key }}"
                    data-question-label="{{ $question['label'] }}"
                    data-required="{{ $required ? 'true' : 'false' }}"
                >
                    <legend>
                        <span class="consultation-question__number" data-visible-question-number>{{ $questionNumber }}</span>
                        <span class="consultation-bilingual-label">
                            <span>{{ $questionLabel['primary'] }} @if($required)<em class="text-danger">*</em>@endif</span>
                            @if ($questionLabel['english'])<small lang="en">{{ $questionLabel['english'] }}</small>@endif
                        </span>
                    </legend>

                    @if ($type === 'yes_no')
                        <div class="consultation-choice-grid">
                            @foreach (['yes' => trans('account::consultation.yes'), 'no' => trans('account::consultation.no')] as $value => $label)
                                <label class="consultation-choice">
                                    <input type="radio" name="answers[{{ $key }}]" value="{{ $value }}" disabled>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif ($type === 'textarea')
                        <textarea class="form-control" name="answers[{{ $key }}]" rows="2" placeholder="{{ $question['placeholder'] ?? '' }}" disabled></textarea>
                    @elseif ($type === 'date')
                        <input class="form-control" type="date" name="answers[{{ $key }}]" disabled>
                    @elseif ($type === 'select')
                        <select class="form-control" name="answers[{{ $key }}]" disabled>
                            <option value="">{{ trans('account::consultation.select_one') }}</option>
                            @foreach (($question['options'] ?? []) as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif ($type === 'checkbox')
                        <div class="consultation-checkbox-list consultation-checkbox-list--medical">
                            @foreach (($question['options'] ?? []) as $option)
                                <label class="consultation-choice consultation-choice--checkbox">
                                    <input type="checkbox" name="answers[{{ $key }}][]" value="{{ $option }}" disabled>
                                    <span class="consultation-choice__box" aria-hidden="true"><i class="fa fa-check" aria-hidden="true"></i></span>
                                    <span class="consultation-choice__label"><i class="fa fa-plus-circle" aria-hidden="true"></i>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif ($type === 'body_map')
                        <div class="consultation-body-map">
                            @include('storefront::public.account.consultations.partials.body-map-figure', [
                                'options' => $question['options'] ?? [],
                                'selectedAreas' => [],
                                'interactive' => false,
                            ])
                            <div class="consultation-body-map__zones">
                                <p class="consultation-body-map__instruction"><i class="las la-hand-pointer" aria-hidden="true"></i> {{ trans('account::consultation.medical.select_areas') }}</p>
                                <div class="consultation-body-zone-list">
                                    @foreach (($question['options'] ?? []) as $optionIndex => $option)
                                        <label class="consultation-body-zone">
                                            <input type="checkbox" name="answers[{{ $key }}][]" value="{{ $option }}" disabled>
                                            <span class="consultation-body-zone__number">{{ $optionIndex + 1 }}</span>
                                            <span class="consultation-body-zone__label">{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <input class="form-control" type="text" name="answers[{{ $key }}]" placeholder="{{ $question['placeholder'] ?? '' }}" disabled>
                    @endif

                    <span class="consultation-field-error" data-validation-error hidden>{{ trans('account::consultation.validation.required_field') }}</span>
                </fieldset>
            @endforeach

            @if ($template->consent_text)
                <section class="consultation-consent-box" data-required-block data-question-label="{{ trans('account::consultation.fields.consent') }}">
                    <label>
                        <input type="checkbox" name="consent_accepted" value="1" disabled>
                        <span>{!! $template->consent_text !!}</span>
                    </label>
                    <span class="consultation-field-error" data-validation-error hidden>{{ trans('account::consultation.validation.required_field') }}</span>
                </section>
            @endif

            <section class="consultation-consent-box" data-required-block data-question-label="{{ trans('account::consultation.fields.legal_consent') }}">
                <label>
                    <input type="checkbox" name="legal_consent_accepted" value="1" disabled>
                    <span>
                        {{ trans('account::consultation.legal.prefix') }}
                        @php
                            $legalDocuments = \Modules\Page\Entities\Page::whereIn('slug', ['privacy-policy', 'terms-conditions'])->get();
                        @endphp
                        @foreach ($legalDocuments as $document)
                            <a href="{{ localized_url(locale(), $document->slug) }}" target="_blank" rel="noopener">{{ $document->name }}</a>{{ ! $loop->last ? trans('account::consultation.legal.joiner') : '' }}
                        @endforeach.
                    </span>
                </label>
                <span class="consultation-field-error" data-validation-error hidden>{{ trans('account::consultation.validation.required_field') }}</span>
            </section>

            <section class="consultation-signature-field" data-signature-block data-question-label="{{ trans('account::consultation.fields.signature') }}">
                <div class="consultation-signature-field__heading">
                    <div><strong>{{ trans('account::consultation.signature') }} <em class="text-danger">*</em></strong><p>{{ trans('account::consultation.signature_instruction') }}</p></div>
                </div>
                <canvas id="signature-pad" width="720" height="220" aria-label="{{ trans('account::consultation.signature') }}" style="background: #f8f9fa;"></canvas>
                <small class="text-muted d-block mt-2"><i class="las la-info-circle"></i> {{ trans('product::consultation_forms.signature_preview_note') }}</small>
            </section>

            <div class="text-center mt-4">
                <a href="{{ route('admin.consultation_forms.edit', $template) }}" class="btn btn-primary">
                    <i class="fa fa-pencil"></i> {{ trans('product::consultation_forms.configure') }}
                </a>
                <a href="{{ route('admin.consultation_forms.index') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> {{ trans('product::consultation_forms.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @vite(['modules/Product/Resources/assets/admin/consultation_forms/preview.css'])
@endpush

@push('scripts')
    @vite(['modules/Product/Resources/assets/admin/consultation_forms/preview.js'])
@endpush