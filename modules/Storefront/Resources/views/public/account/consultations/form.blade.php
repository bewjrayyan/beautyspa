@extends('storefront::public.account.layout')

@section('title', $submission->form_title)

@section('account_breadcrumb')
    <li><a href="{{ route('account.consultations.index') }}">{{ trans('account::consultation.pages.title') }}</a></li>
    <li class="active">{{ trans('account::consultation.pages.form') }}</li>
@endsection

@section('panel')
    <div class="consultation-form-shell">
        <header class="consultation-form-header">
            <div class="consultation-form-header__badge"><i class="las la-heartbeat" aria-hidden="true"></i> {{ trans('account::consultation.medical.confidential') }}</div>
            <span>{{ $submission->treatmentBooking?->product?->name ?: trans('account::consultation.pages.form') }}</span>
            <h1>{{ $submission->form_title }}</h1>
            @if ($submission->form_intro)<p>{{ $submission->form_intro }}</p>@endif
            <small>{{ trans('account::consultation.form_help') }}</small>
        </header>

        <section class="consultation-medical-profile" aria-labelledby="consultation-customer-profile">
            <div class="consultation-medical-profile__icon"><i class="las la-user-injured" aria-hidden="true"></i></div>
            <div class="consultation-medical-profile__content">
                <span>{{ trans('account::consultation.medical.patient_details') }}</span>
                <h2 id="consultation-customer-profile">{{ $submission->customer_name ?: auth()->user()->full_name }}</h2>
                <div class="consultation-medical-profile__grid">
                    <div><i class="las la-envelope"></i><small>{{ trans('account::consultation.medical.email') }}</small><strong>{{ $submission->customer_email ?: auth()->user()->email ?: '—' }}</strong></div>
                    <div><i class="las la-phone"></i><small>{{ trans('account::consultation.medical.phone') }}</small><strong>{{ $submission->customer_phone ?: auth()->user()->phone ?: '—' }}</strong></div>
                    <div><i class="las la-id-card"></i><small>{{ trans('account::consultation.medical.identity') }}</small><strong>{{ auth()->user()->identity_number ?: trans('account::consultation.medical.not_provided') }}</strong></div>
                    <div><i class="las la-calendar"></i><small>{{ trans('account::consultation.medical.appointment') }}</small><strong>{{ $submission->treatmentBooking?->appointment_date?->format('d M Y') ?: '—' }}</strong></div>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ route('account.consultations.store', $submission) }}" id="consultation-form" class="consultation-form" novalidate>
            @csrf

            <div class="consultation-validation-summary @unless($errors->any()) is-hidden @endunless" id="consultation-validation-summary" role="alert" tabindex="-1">
                <i class="las la-exclamation-circle" aria-hidden="true"></i>
                <div>
                    <strong>{{ trans('account::consultation.validation.incomplete_title') }}</strong>
                    <p id="consultation-validation-message">{{ trans('account::consultation.validation.incomplete_help', ['count' => $errors->count()]) }}</p>
                    <ul id="consultation-validation-list">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            @php
                $questionsSnapshot = $submission->questions_snapshot ?: [];
                $conditionalVisibility = app(\Modules\Account\Services\ConsultationConditionEvaluator::class)
                    ->visibilityMap($questionsSnapshot, old('answers', []));
                $questionNumber = 0;
            @endphp
            @foreach ($questionsSnapshot as $index => $question)
                @php
                    $key = $question['key'];
                    $type = $question['type'] ?? 'text';
                    $required = !empty($question['required']);
                    $questionLabel = \Modules\Account\Support\ConsultationQuestionLabel::parts($question);
                @endphp

                @if ($type === 'section')
                    <div class="consultation-medical-section">
                        <span class="consultation-medical-section__icon" aria-hidden="true">
                            <i class="las {{ str_contains($key, 'body') ? 'la-street-view' : (str_contains($key, 'goal') ? 'la-bullseye' : 'la-notes-medical') }}"></i>
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
                    $isVisible = $conditionalVisibility[$key] ?? true;
                    $questionNumber++;
                @endphp
                <fieldset
                    class="consultation-question consultation-question--{{ str_replace('_', '-', $type) }} @if(data_get($question, 'condition.enabled', false)) consultation-question--followup @endif @if($errors->has("answers.{$key}")) has-error @endif"
                    data-question-key="{{ $key }}"
                    data-question-label="{{ $question['label'] }}"
                    data-required="{{ $required ? 'true' : 'false' }}"
                    data-condition-enabled="{{ data_get($question, 'condition.enabled', false) ? 'true' : 'false' }}"
                    data-condition-source="{{ data_get($question, 'condition.source_key', '') }}"
                    data-condition-operator="{{ data_get($question, 'condition.operator', 'equals') }}"
                    data-condition-value="{{ data_get($question, 'condition.value', '') }}"
                    @unless($isVisible) hidden @endunless
                >
                    <legend>
                        <span class="consultation-question__number" data-visible-question-number>{{ $questionNumber }}</span>
                        <span class="consultation-bilingual-label">
                            <span>{{ $questionLabel['primary'] }} @if($required)<em>*</em>@endif</span>
                            @if ($questionLabel['english'])<small lang="en">{{ $questionLabel['english'] }}</small>@endif
                        </span>
                    </legend>

                    @if ($type === 'yes_no')
                        <div class="consultation-choice-grid">
                            @foreach (['yes' => trans('account::consultation.yes'), 'no' => trans('account::consultation.no')] as $value => $label)
                                <label class="consultation-choice">
                                    <input type="radio" name="answers[{{ $key }}]" value="{{ $value }}" @checked(old("answers.{$key}") === $value)>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif ($type === 'textarea')
                        <textarea class="form-control" name="answers[{{ $key }}]" rows="2" placeholder="{{ $question['placeholder'] ?? '' }}">{{ old("answers.{$key}") }}</textarea>
                    @elseif ($type === 'date')
                        <input class="form-control" type="date" name="answers[{{ $key }}]" value="{{ old("answers.{$key}") }}">
                    @elseif ($type === 'select')
                        <select class="form-control" name="answers[{{ $key }}]">
                            <option value="">{{ trans('account::consultation.select_one') }}</option>
                            @foreach (($question['options'] ?? []) as $option)
                                <option value="{{ $option }}" @selected(old("answers.{$key}") === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif ($type === 'checkbox')
                        <div class="consultation-checkbox-list consultation-checkbox-list--medical">
                            @foreach (($question['options'] ?? []) as $option)
                                <label class="consultation-choice">
                                    <input type="checkbox" name="answers[{{ $key }}][]" value="{{ $option }}" @checked(in_array($option, old("answers.{$key}", [])))>
                                    <span><i class="las la-plus-circle" aria-hidden="true"></i>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif ($type === 'body_map')
                        <div class="consultation-body-map">
                            @include('storefront::public.account.consultations.partials.body-map-figure', [
                                'options' => $question['options'] ?? [],
                                'selectedAreas' => old("answers.{$key}", []),
                                'interactive' => true,
                            ])
                            <div class="consultation-body-map__zones">
                                <p><i class="las la-hand-pointer"></i> {{ trans('account::consultation.medical.select_areas') }}</p>
                                <div>
                                    @foreach (($question['options'] ?? []) as $optionIndex => $option)
                                        <label class="consultation-body-zone">
                                            <input type="checkbox" name="answers[{{ $key }}][]" value="{{ $option }}" data-body-marker-toggle="{{ sha1($option) }}" @checked(in_array($option, old("answers.{$key}", [])))>
                                            <span><b>{{ $optionIndex + 1 }}</b>{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <input class="form-control" type="text" name="answers[{{ $key }}]" value="{{ old("answers.{$key}") }}" placeholder="{{ $question['placeholder'] ?? '' }}">
                    @endif

                    {!! $errors->first("answers.{$key}", '<span class="help-block text-red">:message</span>') !!}
                    <span class="consultation-field-error" data-validation-error hidden>{{ trans('account::consultation.validation.required_field') }}</span>
                </fieldset>
            @endforeach

            <section class="consultation-consent-box @if($errors->has('consent_accepted')) has-error @endif" data-required-block data-question-label="{{ trans('account::consultation.fields.consent') }}">
                <label>
                    <input type="checkbox" name="consent_accepted" value="1" @checked(old('consent_accepted'))>
                    <span>{{ $submission->consent_text }}</span>
                </label>
                {!! $errors->first('consent_accepted', '<span class="help-block text-red">:message</span>') !!}
                <span class="consultation-field-error" data-validation-error hidden>{{ trans('account::consultation.validation.required_field') }}</span>
            </section>

            <section class="consultation-consent-box @if($errors->has('legal_consent_accepted')) has-error @endif" data-required-block data-question-label="{{ trans('account::consultation.fields.legal_consent') }}">
                <label>
                    <input type="checkbox" name="legal_consent_accepted" value="1" @checked(old('legal_consent_accepted'))>
                    <span>
                        {{ trans('account::consultation.legal.prefix') }}
                        @foreach ($legalDocuments as $document)
                            <a href="{{ localized_url(locale(), $document->slug) }}" target="_blank" rel="noopener">{{ $document->name }}</a>{{ ! $loop->last ? trans('account::consultation.legal.joiner') : '' }}
                        @endforeach.
                    </span>
                </label>
                {!! $errors->first('legal_consent_accepted', '<span class="help-block text-red">:message</span>') !!}
                <span class="consultation-field-error" data-validation-error hidden>{{ trans('account::consultation.validation.required_field') }}</span>
            </section>

            <section class="consultation-signature-field @if($errors->has('signature_data')) has-error @endif" data-signature-block data-question-label="{{ trans('account::consultation.fields.signature') }}">
                <div class="consultation-signature-field__heading">
                    <div><strong>{{ trans('account::consultation.signature') }} *</strong><p>{{ trans('account::consultation.signature_instruction') }}</p></div>
                    <button type="button" class="btn btn-default btn-sm" id="clear-signature">{{ trans('account::consultation.clear_signature') }}</button>
                </div>
                <canvas id="signature-pad" width="720" height="220" aria-label="{{ trans('account::consultation.signature') }}"></canvas>
                <input type="hidden" name="signature_data" id="signature-data" value="">
                <span class="help-block text-red" id="signature-error">{{ $errors->first('signature_data') }}</span>
            </section>

            <button type="submit" class="btn btn-primary consultation-submit">
                <i class="las la-file-signature"></i> {{ trans('account::consultation.submit') }}
            </button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('consultation-form');
    const canvas = document.getElementById('signature-pad');
    const context = canvas.getContext('2d');
    const hidden = document.getElementById('signature-data');
    const error = document.getElementById('signature-error');
    let drawing = false;
    let signed = false;

    function normalizeConditionValue(value) {
        const normalized = String(value == null ? '' : value).trim().toLocaleLowerCase();

        if (normalized === 'ya') return 'yes';
        if (normalized === 'tidak') return 'no';

        return normalized;
    }

    function answerFor(key) {
        const scalarName = `answers[${key}]`;
        const arrayName = `${scalarName}[]`;
        const fields = Array.from(form.elements).filter(function (field) {
            return field.name === scalarName || field.name === arrayName;
        });
        const choices = fields.filter(field => field.type === 'radio' || field.type === 'checkbox');

        if (choices.length) {
            const selected = choices.filter(field => field.checked).map(field => field.value);
            return fields.some(field => field.name === arrayName) ? selected : (selected[0] || '');
        }

        return fields[0] ? fields[0].value : '';
    }

    function conditionMatches(answer, operator, expected) {
        const normalizedExpected = normalizeConditionValue(expected);
        const hasAnswer = Array.isArray(answer)
            ? answer.some(value => normalizeConditionValue(value) !== '')
            : normalizeConditionValue(answer) !== '';

        if (!hasAnswer) return false;

        let matches;

        if (operator === 'contains' || operator === 'not_contains') {
            matches = Array.isArray(answer)
                ? answer.some(value => normalizeConditionValue(value) === normalizedExpected)
                : normalizeConditionValue(answer).includes(normalizedExpected);
        } else {
            matches = Array.isArray(answer)
                ? answer.length === 1 && normalizeConditionValue(answer[0]) === normalizedExpected
                : normalizeConditionValue(answer) === normalizedExpected;
        }

        return ['not_equals', 'not_contains'].includes(operator) ? !matches : matches;
    }

    function refreshConditionalFields() {
        const visibility = {};
        let visibleNumber = 0;

        form.querySelectorAll('[data-question-key]').forEach(function (block) {
            const key = block.dataset.questionKey;
            const enabled = block.dataset.conditionEnabled === 'true';
            const source = block.dataset.conditionSource;
            const visible = !enabled || (visibility[source] === true && conditionMatches(
                answerFor(source),
                block.dataset.conditionOperator,
                block.dataset.conditionValue
            ));

            visibility[key] = visible;
            block.hidden = !visible;
            block.querySelectorAll('input, textarea, select').forEach(field => field.disabled = !visible);

            if (visible) {
                visibleNumber++;
                const number = block.querySelector('[data-visible-question-number]');
                if (number) number.textContent = visibleNumber;
            } else {
                setInvalid(block, false);
            }
        });
    }

    function syncBodyMarker(toggle) {
        document.querySelectorAll('[data-body-marker="' + toggle.dataset.bodyMarkerToggle + '"]').forEach(function (marker) {
            marker.classList.toggle('is-visible', toggle.checked);
        });
    }

    document.querySelectorAll('[data-body-marker-toggle]').forEach(function (toggle) {
        syncBodyMarker(toggle);
        toggle.addEventListener('change', function () { syncBodyMarker(toggle); });
    });

    context.lineWidth = 2.4;
    context.lineCap = 'round';
    context.strokeStyle = '#231820';

    function point(event) {
        const rect = canvas.getBoundingClientRect();
        const source = event.touches ? event.touches[0] : event;
        return { x: (source.clientX - rect.left) * canvas.width / rect.width, y: (source.clientY - rect.top) * canvas.height / rect.height };
    }
    function start(event) { event.preventDefault(); drawing = true; signed = true; const p = point(event); context.beginPath(); context.moveTo(p.x, p.y); }
    function move(event) { if (!drawing) return; event.preventDefault(); const p = point(event); context.lineTo(p.x, p.y); context.stroke(); }
    function stop() { drawing = false; }

    ['mousedown', 'touchstart'].forEach(name => canvas.addEventListener(name, start, { passive: false }));
    ['mousemove', 'touchmove'].forEach(name => canvas.addEventListener(name, move, { passive: false }));
    ['mouseup', 'mouseleave', 'touchend'].forEach(name => canvas.addEventListener(name, stop));

    document.getElementById('clear-signature').addEventListener('click', function () {
        context.clearRect(0, 0, canvas.width, canvas.height); signed = false; hidden.value = ''; error.textContent = '';
    });

    function setInvalid(block, invalid) {
        block.classList.toggle('has-error', invalid);
        block.querySelectorAll('input, textarea, select, canvas').forEach(function (field) {
            field.setAttribute('aria-invalid', invalid ? 'true' : 'false');
        });
        const message = block.querySelector('[data-validation-error]');
        if (message) message.hidden = !invalid;
    }

    function hasAnswer(block) {
        const choices = block.querySelectorAll('input[type="radio"], input[type="checkbox"]');
        if (choices.length) return Array.from(choices).some(input => input.checked);
        const field = block.querySelector('textarea, select, input:not([type="hidden"])');
        return field && field.value.trim() !== '';
    }

    function validateForm() {
        const invalid = [];

        form.querySelectorAll('[data-required="true"]:not([hidden])').forEach(function (block) {
            const isInvalid = !hasAnswer(block);
            setInvalid(block, isInvalid);
            if (isInvalid) invalid.push(block);
        });

        form.querySelectorAll('[data-required-block]').forEach(function (consent) {
            const consentInvalid = !consent.querySelector('input[type="checkbox"]').checked;
            setInvalid(consent, consentInvalid);
            if (consentInvalid) invalid.push(consent);
        });

        const signatureBlock = form.querySelector('[data-signature-block]');
        setInvalid(signatureBlock, !signed);
        error.textContent = signed ? '' : @json(trans('account::consultation.validation.signature_required'));
        if (!signed) invalid.push(signatureBlock);

        const summary = document.getElementById('consultation-validation-summary');
        const list = document.getElementById('consultation-validation-list');
        list.replaceChildren();

        invalid.forEach(function (block) {
            const item = document.createElement('li');
            item.textContent = block.dataset.questionLabel;
            list.appendChild(item);
        });

        summary.classList.toggle('is-hidden', invalid.length === 0);
        document.getElementById('consultation-validation-message').textContent = @json(trans('account::consultation.validation.incomplete_help', ['count' => '__COUNT__'])).replace('__COUNT__', invalid.length);

        if (invalid.length) {
            summary.focus();
            summary.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return invalid.length === 0;
    }

    form.addEventListener('input', function (event) {
        refreshConditionalFields();
        const block = event.target.closest('[data-required="true"], [data-required-block]');
        if (block && hasAnswer(block)) setInvalid(block, false);
    });

    form.addEventListener('change', refreshConditionalFields);

    form.addEventListener('submit', function (event) {
        refreshConditionalFields();
        if (!validateForm()) { event.preventDefault(); return; }
        const exportCanvas = document.createElement('canvas');
        exportCanvas.width = canvas.width;
        exportCanvas.height = canvas.height;
        const exportContext = exportCanvas.getContext('2d');
        exportContext.fillStyle = '#ffffff';
        exportContext.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
        exportContext.drawImage(canvas, 0, 0);
        hidden.value = exportCanvas.toDataURL('image/png');
    });

    refreshConditionalFields();
});
</script>
@endpush
