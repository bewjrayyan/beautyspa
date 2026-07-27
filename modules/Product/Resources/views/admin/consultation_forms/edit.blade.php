@extends('admin::layout')

@php
    $questions = collect(old('questions', $template->questions ?: []))->map(function ($question) {
        $question['options'] = is_array($question['options'] ?? null)
            ? implode("\n", $question['options'])
            : ($question['options'] ?? '');

        return $question;
    })->values();
    $followupLabels = [
        'answer' => trans('product::consultation_forms.followup_answer'),
        'chooseAnswer' => trans('product::consultation_forms.choose_followup_answer'),
        'question' => trans('product::consultation_forms.followup_question'),
        'questionPlaceholder' => trans('product::consultation_forms.followup_question_placeholder'),
        'type' => trans('product::consultation_forms.followup_type'),
        'placeholder' => trans('product::consultation_forms.followup_placeholder'),
        'placeholderExample' => trans('product::consultation_forms.followup_placeholder_example'),
        'required' => trans('product::consultation_forms.followup_required'),
        'remove' => trans('product::consultation_forms.remove_followup'),
        'badge' => trans('product::consultation_forms.followup_badge'),
    ];
    $followupTypeLabels = collect(['yes_no', 'text', 'textarea', 'date', 'select', 'checkbox', 'body_map'])
        ->mapWithKeys(fn ($type) => [
            $type => trans("product::consultation_forms.types.{$type}"),
        ])->all();
@endphp

@component('admin::components.page.header')
    @slot('title', trans('product::consultation_forms.edit_title'))
    @slot('subtitle', trans('product::consultation_forms.template_help'))

    <li><a href="{{ route('admin.consultation_forms.index') }}">{{ trans('product::consultation_forms.title') }}</a></li>
    <li class="active">{{ $template->name }}</li>
@endcomponent

@section('content')
    <form class="consultation-editor" method="POST" action="{{ route('admin.consultation_forms.update', $template) }}">
        @csrf
        @method('PUT')

        <header class="consultation-editor__hero">
            <a href="{{ route('admin.consultation_forms.index') }}"><i class="fa fa-arrow-left"></i> {{ trans('product::consultation_forms.back') }}</a>
            <div class="consultation-editor__hero-main">
                <span class="consultation-editor__hero-icon"><i class="fa fa-file-text-o"></i></span>
                <div><span>{{ trans('product::consultation_forms.template') }}</span><h2>{{ $template->name }}</h2><p>{{ trans('product::consultation_forms.version', ['version' => max(1, (int) $template->version)]) }}</p></div>
            </div>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger"><strong>{{ $errors->first() }}</strong></div>
        @endif

        <section class="consultation-editor__section consultation-editor__status">
            <div><h3>{{ trans('product::consultation_forms.status_title') }}</h3><p>{{ trans('product::consultation_forms.status_help') }}</p></div>
            <label class="consultation-switch">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $template->is_active))>
                <span aria-hidden="true"></span>
                <strong>{{ trans('product::consultation_forms.enabled') }}</strong>
            </label>
        </section>

        <section class="consultation-editor__section">
            <div class="consultation-editor__heading"><div><h3>{{ trans('product::consultation_forms.form_details') }}</h3><p>{{ trans('product::consultation_forms.form_details_help') }}</p></div></div>
            <div class="consultation-editor__fields">
                <div class="form-group @error('name') has-error @enderror"><label for="consultation-name">{{ trans('product::consultation_forms.internal_name') }} *</label><input id="consultation-name" class="form-control" name="name" value="{{ old('name', $template->name) }}" maxlength="190"></div>
                <div class="form-group @error('title') has-error @enderror"><label for="consultation-title">{{ trans('product::consultation_forms.form_title') }} *</label><input id="consultation-title" class="form-control" name="title" value="{{ old('title', $template->title) }}" maxlength="190"></div>
                <div class="form-group @error('intro') has-error @enderror"><label for="consultation-intro">{{ trans('product::consultation_forms.intro') }}</label><textarea id="consultation-intro" class="form-control" name="intro" rows="4">{{ old('intro', $template->intro) }}</textarea></div>
            </div>
        </section>

        <div class="consultation-editor__workspace">
            <div class="consultation-editor__builder">
                <section class="consultation-editor__section">
                    <div class="consultation-editor__heading"><div><h3>{{ trans('product::consultation_forms.questions_title') }}</h3><p>{{ trans('product::consultation_forms.questions_help') }}</p></div><button class="btn btn-primary" id="add-consultation-question" type="button"><i class="fa fa-plus"></i> {{ trans('product::consultation_forms.add_question') }}</button></div>

                    <div id="consultation-questions">
                        @foreach ($questions as $index => $question)
                            @include('product::admin.consultation_forms.partials.question', ['index' => $index, 'question' => $question])
                        @endforeach
                    </div>

                    <div class="consultation-editor__empty" id="consultation-questions-empty" @if($questions->isNotEmpty()) hidden @endif><i class="fa fa-list-alt"></i><p>{{ trans('product::consultation_forms.no_questions') }}</p></div>
                </section>

                <section class="consultation-editor__section">
                    <div class="consultation-editor__heading"><div><h3>{{ trans('product::consultation_forms.consent') }}</h3><p>{{ trans('product::consultation_forms.consent_help') }}</p></div></div>
                    <div class="form-group @error('consent') has-error @enderror"><textarea class="form-control" name="consent" rows="5" data-consent-input>{{ old('consent', $template->consent_text) }}</textarea></div>
                </section>
            </div>
        </div>

        <footer class="consultation-editor__footer"><a class="btn btn-default" href="{{ route('admin.consultation_forms.index') }}">{{ trans('product::consultation_forms.cancel') }}</a><button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> {{ trans('product::consultation_forms.save') }}</button></footer>
    </form>

    <template id="consultation-question-template">
        @include('product::admin.consultation_forms.partials.question', ['index' => '__INDEX__', 'question' => ['key' => '__KEY__', 'label' => '', 'type' => 'yes_no', 'placeholder' => '', 'required' => true, 'options' => '', 'condition' => ['enabled' => false, 'source_key' => '', 'operator' => 'equals', 'value' => '']]])
    </template>

@endsection

@push('styles')
    <style>
        .consultation-editor{max-width:1420px;margin:0 auto 45px}.consultation-editor__hero{margin-bottom:20px;padding:22px 24px;border:1px solid #eadfe6;border-radius:16px;background:linear-gradient(135deg,#fff6fb,#fff)}.consultation-editor__hero>a{display:inline-block;margin-bottom:18px;color:#8d0a50;font-size:12px;font-weight:700}.consultation-editor__hero-main{display:flex;align-items:center;gap:15px}.consultation-editor__hero-icon{display:grid;place-items:center;width:48px;height:48px;border-radius:13px;color:#fff;background:#a20759;font-size:20px}.consultation-editor__hero-main span{color:#8d8289;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}.consultation-editor__hero-main h2{margin:2px 0;font-size:21px}.consultation-editor__hero-main p{margin:0;color:#7a7177;font-size:12px}.consultation-editor__section{margin-bottom:18px;padding:24px;border:1px solid #e5e0e3;border-radius:15px;background:#fff;box-shadow:0 6px 22px rgba(40,27,34,.04)}.consultation-editor__status,.consultation-editor__heading{display:flex;align-items:center;justify-content:space-between;gap:25px}.consultation-editor__section h3{margin:0 0 5px;font-size:17px}.consultation-editor__section p{margin:0;color:#7d747a}.consultation-editor__heading{margin-bottom:20px}.consultation-editor__fields{display:grid;grid-template-columns:1fr;gap:3px}.consultation-editor label{display:block;margin-bottom:7px}.consultation-switch{display:flex!important;align-items:center;gap:10px;margin:0!important;white-space:nowrap}.consultation-switch input[type=checkbox]{position:absolute;opacity:0}.consultation-switch span{position:relative;width:44px;height:24px;border-radius:999px;background:#d5d1d4;transition:.2s}.consultation-switch span:after{content:"";position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.18);transition:.2s}.consultation-switch input:checked+span{background:#a20759}.consultation-switch input:checked+span:after{transform:translateX(20px)}.consultation-editor__workspace{display:grid;grid-template-columns:minmax(0,1fr) 430px;align-items:start;gap:20px}.consultation-question-row{position:relative;margin-bottom:13px;padding:18px;border:1px solid #e7e1e5;border-radius:13px;background:#fbfafb}.consultation-question-row.is-section{border-color:#e8bfd5;background:#fff5fa}.consultation-question-row.is-section .consultation-question-row__number{background:#7b174d}.consultation-question-row.is-body-map{border-color:#cabfe7;background:#faf8ff}.consultation-question-row__top{display:grid;grid-template-columns:34px minmax(0,1fr) 190px auto;align-items:end;gap:12px}.consultation-question-row__number{display:grid;place-items:center;width:32px;height:32px;margin-bottom:2px;border-radius:9px;color:#fff;background:#a20759;font-weight:700}.consultation-question-row__actions{display:flex;gap:5px}.consultation-question-row__actions .btn{width:34px;height:34px;padding:0}.consultation-question-row__options{margin:14px 46px 0}.consultation-question-row__required{margin:13px 0 0 46px!important;font-weight:500}.consultation-question-row__required input{margin-right:6px}.consultation-editor__empty{padding:30px;border:1px dashed #d8d1d5;border-radius:12px;color:#81777e;background:#faf9fa;text-align:center}.consultation-editor__empty i{font-size:23px}.consultation-editor__empty p{margin-top:7px}.consultation-editor__footer{position:sticky;bottom:0;z-index:5;display:flex;justify-content:flex-end;gap:9px;padding:15px 0;background:linear-gradient(180deg,rgba(245,245,245,0),#f5f5f5 28%)}.consultation-editor__footer .btn{min-width:140px;border-radius:9px}
        .consultation-form-preview{position:sticky;top:78px;max-height:calc(100vh - 95px);overflow-y:auto;padding:18px;border:1px solid #dfd6dc;border-radius:16px;background:#f7f3f5;box-shadow:0 12px 34px rgba(42,28,36,.08)}.consultation-form-preview__heading{display:flex;align-items:center;justify-content:space-between;gap:15px}.consultation-form-preview__heading span{color:#a20759;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.consultation-form-preview__heading h3{margin:3px 0 0;font-size:17px}.consultation-form-preview__heading>i{color:#a20759;font-size:24px}.consultation-form-preview>p{margin:7px 0 15px;color:#756c72;font-size:12px}.consultation-form-preview__paper{overflow:hidden;border:1px solid #e3dce0;border-radius:12px;background:#fff;box-shadow:0 5px 18px rgba(40,25,34,.05)}.consultation-form-preview__paper>header{padding:20px;background:linear-gradient(135deg,#8c0b50,#b81370);color:#fff}.consultation-form-preview__paper>header small{display:block;margin-bottom:6px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;opacity:.78}.consultation-form-preview__paper>header h2{margin:0 0 6px;font-size:19px;line-height:1.25}.consultation-form-preview__paper>header p{margin:0;color:#fff;font-size:11px;line-height:1.5;opacity:.86}.consultation-form-preview__patient{padding:16px;border-bottom:1px solid #eee6eb;background:#fff9fc}.consultation-form-preview__patient>strong{display:block;margin-bottom:10px;color:#7f134e;font-size:12px}.consultation-form-preview__patient>div{display:grid;grid-template-columns:1fr 1fr;gap:7px}.consultation-form-preview__patient span{min-height:30px;padding:8px;border:1px solid #eadfe5;border-radius:7px;color:#8a8186;background:#fff;font-size:9px}.consultation-form-preview__question{padding:14px 16px;border-bottom:1px solid #eee9ec}.consultation-form-preview__question>strong{display:block;margin-bottom:9px;color:#292129;font-size:11px;line-height:1.35}.consultation-form-preview__question>strong em{color:#bd126d}.consultation-form-preview__section{display:flex;align-items:center;gap:9px;padding:12px 16px;color:#fff;background:#531a3b}.consultation-form-preview__section i{display:grid;place-items:center;width:24px;height:24px;border-radius:7px;color:#7d164d;background:#fff}.consultation-form-preview__section strong{font-size:12px}.consultation-form-preview__choices{display:grid;grid-template-columns:1fr 1fr;gap:6px}.consultation-form-preview__choices span,.consultation-form-preview__option{padding:7px 8px;border:1px solid #e7dfe4;border-radius:7px;color:#6e656a;background:#fbfafb;font-size:9px}.consultation-form-preview__option:before{content:"";display:inline-block;width:9px;height:9px;margin-right:6px;border:1px solid #a899a2;border-radius:2px;background:#fff;vertical-align:-1px}.consultation-form-preview__line{height:34px;border:1px solid #e7dfe4;border-radius:7px;background:repeating-linear-gradient(#fff,#fff 15px,#f0e8ed 16px)}.consultation-form-preview__signature{padding:16px;background:#fff9fc}.consultation-form-preview__signature strong{display:block;color:#7f134e;font-size:11px}.consultation-form-preview__signature p{margin:7px 0;color:#766b71;font-size:9px;line-height:1.45}.consultation-form-preview__signature>span{display:block;height:40px;border-bottom:1px solid #a99ca3}.consultation-preview-body-map{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:10px;border:1px solid #e5dce2;border-radius:10px;background:#fff9fc}.consultation-preview-body-map__figure{text-align:center}.consultation-preview-body-map__figure strong,.consultation-preview-body-map__figure span{display:block}.consultation-preview-body-map__figure strong{margin-bottom:4px;color:#761044;font-size:9px;text-transform:uppercase}.consultation-preview-body-map__figure span{margin-top:3px;color:#8a7d84;font-size:8px}.consultation-preview-body-map__figure svg{display:block;width:100%;height:175px}.consultation-preview-body-map__silhouette{fill:#f7dce9;stroke:#7c2855;stroke-linecap:round;stroke-linejoin:round;stroke-width:9}.consultation-preview-body-map__silhouette circle,.consultation-preview-body-map__silhouette path:nth-child(2),.consultation-preview-body-map__silhouette path:nth-child(3){stroke-width:2.2}.consultation-preview-body-map__details{fill:#b51668;stroke:#b51668;stroke-linecap:round;stroke-width:1.6}.consultation-preview-body-map__details path{fill:none}
        @media(max-width:1199px){.consultation-editor__workspace{grid-template-columns:minmax(0,1fr) 370px}.consultation-question-row__top{grid-template-columns:34px minmax(0,1fr)}.consultation-question-row__type,.consultation-question-row__actions{grid-column:2}}
        @media(max-width:991px){.consultation-editor__workspace{grid-template-columns:1fr}.consultation-form-preview{position:static;order:-1;max-height:none;overflow:visible}.consultation-form-preview__paper{max-width:520px;margin:auto}}
        @media(max-width:767px){.consultation-editor__status,.consultation-editor__heading{align-items:stretch;flex-direction:column}.consultation-switch{align-self:flex-start}.consultation-question-row__top{grid-template-columns:34px 1fr}.consultation-question-row__type,.consultation-question-row__actions{grid-column:2}.consultation-question-row__options,.consultation-question-row__required{margin-left:46px}.consultation-editor__section{padding:18px}.consultation-form-preview{padding:14px}}
        .consultation-editor__workspace{display:block}.consultation-question-row__body-map{margin:18px 46px 4px;padding:20px;border:1px solid #dacde8;border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(78,46,96,.06)}.consultation-question-row__body-map-heading{display:flex;align-items:center;gap:12px;margin-bottom:17px;padding-bottom:14px;border-bottom:1px solid #eee7f2}.consultation-question-row__body-map-heading>span{display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border-radius:11px;color:#fff;background:#7c2855;font-size:18px}.consultation-question-row__body-map-heading strong{display:block;margin-bottom:3px;color:#4b2340;font-size:14px}.consultation-question-row__body-map-heading p{margin:0;color:#766b73;font-size:11px;line-height:1.45}.consultation-question-row__body-map-layout{display:grid;grid-template-columns:minmax(0,2fr) minmax(220px,1fr);align-items:start;gap:18px}.consultation-question-row__body-map .consultation-preview-body-map{padding:16px;background:#fff8fc}.consultation-question-row__body-map .consultation-preview-body-map__figure strong{font-size:11px}.consultation-question-row__body-map .consultation-preview-body-map__figure span{font-size:10px}.consultation-question-row__body-map .consultation-preview-body-map__figure img{display:block;width:100%;height:330px;object-fit:contain}.consultation-question-row__body-zones{padding:16px;border:1px solid #eee4ea;border-radius:11px;background:#fbfafb}.consultation-question-row__body-zones>strong{display:block;margin-bottom:11px;color:#6c1947;font-size:12px}.consultation-question-row__body-zones>div{display:flex;flex-wrap:wrap;gap:7px}.consultation-question-row__body-zones span{padding:7px 9px;border:1px solid #e2d4dc;border-radius:999px;color:#665960;background:#fff;font-size:10px}.consultation-question-row__body-zones span:before{content:"\f041";margin-right:5px;color:#b50863;font-family:FontAwesome}
        .consultation-question-row__body-map .consultation-preview-body-map{display:block}.consultation-preview-body-map__labels{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:8px}.consultation-preview-body-map__labels span{color:#721844;font-size:9px;font-weight:800;text-align:center;text-transform:uppercase}.consultation-question-row__body-map .consultation-preview-body-map>img{display:block;width:100%;height:auto;max-height:520px;object-fit:contain}.consultation-preview-body-map__instruction{display:block;margin-top:8px;color:#756a70;font-size:10px;text-align:center}
        .consultation-question-row__options textarea{resize:vertical;line-height:1.55}.consultation-question-row__options-count{display:block;margin-top:7px;color:#8b6e7e;font-size:10px;font-weight:700}
        .consultation-question-row__followup{margin:16px 46px 0;padding:15px 16px;border:1px solid #e8dce3;border-radius:11px;background:#fff}.consultation-question-row__followup:not(.has-rules){padding:10px 14px;background:#faf9fa}.consultation-question-row__followup:not(.has-rules) .consultation-question-row__followup-heading p{display:none}.consultation-question-row__followup-heading{display:flex;align-items:center;justify-content:space-between;gap:18px}.consultation-question-row__followup-heading strong{display:block;margin-bottom:3px;color:#5c334a;font-size:12px}.consultation-question-row__followup-heading strong i{margin-right:5px;color:#a20759}.consultation-question-row__followup-heading p{font-size:11px}.consultation-question-row__followup-heading .btn{white-space:nowrap}.consultation-question-row__followup-rules{display:grid;gap:12px;margin-top:13px}.consultation-followup-rule{padding:16px;border:1px solid #e5c6d7;border-radius:12px;background:#fff8fc}.consultation-followup-rule__badge{display:inline-flex;align-items:center;gap:6px;margin-bottom:13px;padding:5px 9px;border-radius:999px;color:#8f0b50;background:#f7dce9;font-size:10px;font-weight:800;text-transform:uppercase}.consultation-followup-rule__grid{display:grid;grid-template-columns:minmax(170px,.8fr) minmax(0,1.5fr) minmax(170px,.8fr);align-items:end;gap:12px}.consultation-followup-rule .form-group{margin:0}.consultation-followup-rule label{color:#5f5058;font-size:11px}.consultation-followup-rule__placeholder{grid-column:2/4}.consultation-followup-rule__footer{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-top:14px;padding-top:12px;border-top:1px solid #eadfe5}.consultation-followup-rule__required{display:flex!important;align-items:center;gap:7px;margin:0!important;color:#51454c!important;font-size:11px!important}.consultation-followup-rule__required input{margin:0}.consultation-followup-rule__remove{white-space:nowrap}.consultation-question-row[data-inline-followup-target="true"]{display:none!important}
        @media(max-width:767px){.consultation-question-row__body-map,.consultation-question-row__followup{margin-left:0;margin-right:0}.consultation-question-row__body-map-layout,.consultation-followup-rule__grid{grid-template-columns:1fr}.consultation-followup-rule__placeholder{grid-column:auto}.consultation-question-row__body-map .consultation-preview-body-map__figure img{height:250px}.consultation-question-row__followup-heading{align-items:flex-start;flex-direction:column}.consultation-followup-rule__footer{align-items:flex-start;flex-direction:column}.consultation-followup-rule__remove{align-self:flex-end}}
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const container = document.getElementById('consultation-questions');
            const empty = document.getElementById('consultation-questions-empty');
            const template = document.getElementById('consultation-question-template');
            const yesValue = @json(trans('product::consultation_forms.conditional_yes_value'));
            const noValue = @json(trans('product::consultation_forms.conditional_no_value'));
            const followupLabels = @json($followupLabels);
            const typeLabels = @json($followupTypeLabels);

            function updateBodyMap(row) {
                const type = row.querySelector('[data-question-type]').value;
                const bodyMap = row.querySelector('[data-question-body-map]');
                const zones = row.querySelector('[data-question-body-zones]');
                const optionsInput = row.querySelector('[data-question-options-input]');
                const optionsCount = row.querySelector('[data-question-options-count]');
                const options = optionsInput.value
                    .split('\n').map((option) => option.trim()).filter(Boolean);

                optionsInput.rows = Math.max(4, Math.min(24, options.length || 4));
                optionsCount.textContent = optionsCount.dataset.countTemplate.replace('__COUNT__', options.length);
                optionsCount.hidden = !['select', 'checkbox', 'body_map'].includes(type);
                bodyMap.hidden = type !== 'body_map';
                zones.replaceChildren();

                if (type !== 'body_map') return;

                options.forEach((option) => {
                        const chip = document.createElement('span');
                        chip.textContent = option;
                        zones.appendChild(chip);
                    });
            }

            function questionKey(row) {
                return row.querySelector('input[name$="[key]"]').value;
            }

            function conditionFields(row) {
                return {
                    enabled: row.querySelector('[data-condition-enabled]'),
                    source: row.querySelector('[data-condition-source]'),
                    operator: row.querySelector('[data-condition-operator]'),
                    value: row.querySelector('[data-condition-value]'),
                };
            }

            function clearCondition(row) {
                const condition = conditionFields(row);
                condition.enabled.value = '0';
                condition.source.value = '';
                condition.operator.value = 'equals';
                condition.value.value = '';
            }

            function setCondition(row, sourceKey, operator, value) {
                const condition = conditionFields(row);
                condition.enabled.value = '1';
                condition.source.value = sourceKey;
                condition.operator.value = operator;
                condition.value.value = value;
            }

            function placeholderField(row) {
                return row.querySelector('[data-question-placeholder]');
            }

            function answerChoices(row) {
                const type = row.querySelector('[data-question-type]').value;

                if (type === 'yes_no') {
                    return [{ value: 'yes', label: yesValue }, { value: 'no', label: noValue }];
                }

                if (!['select', 'checkbox', 'body_map'].includes(type)) return [];

                return [...new Set(row.querySelector('[data-question-options-input]').value
                    .split('\n').map(value => value.trim()).filter(Boolean))]
                    .map(value => ({ value, label: value }));
            }

            function conditionOperatorFor(row) {
                const type = row.querySelector('[data-question-type]').value;

                return ['checkbox', 'body_map'].includes(type) ? 'contains' : 'equals';
            }

            function selectControl(options, selected, placeholder) {
                const select = document.createElement('select');
                select.className = 'form-control';
                select.appendChild(new Option(placeholder, ''));
                options.forEach(option => select.appendChild(new Option(option.label, option.value)));
                select.value = selected;

                return select;
            }

            function fieldGroup(labelText, control, className = '') {
                const group = document.createElement('div');
                const label = document.createElement('label');
                group.className = `form-group ${className}`.trim();
                label.textContent = labelText;
                label.appendChild(control);
                group.appendChild(label);

                return group;
            }

            function normalizedExpected(value) {
                const normalized = value.trim().toLocaleLowerCase();
                if (normalized === 'ya') return 'yes';
                if (normalized === 'tidak') return 'no';

                return value;
            }

            function appendFollowupEditor(sourceRow, targetRow) {
                const targetKey = questionKey(targetRow);
                const condition = conditionFields(targetRow);
                const rule = document.createElement('div');
                const choices = answerChoices(sourceRow);
                let trigger;

                if (choices.length) {
                    trigger = selectControl(choices, normalizedExpected(condition.value.value), followupLabels.chooseAnswer);
                } else {
                    trigger = document.createElement('input');
                    trigger.className = 'form-control';
                    trigger.type = sourceRow.querySelector('[data-question-type]').value === 'date' ? 'date' : 'text';
                    trigger.value = condition.value.value;
                    trigger.placeholder = followupLabels.chooseAnswer;
                }
                trigger.dataset.inlineTrigger = '';

                const label = document.createElement('input');
                label.className = 'form-control';
                label.type = 'text';
                label.maxLength = 500;
                label.value = targetRow.querySelector('input[name$="[label]"]').value;
                label.placeholder = followupLabels.questionPlaceholder;
                label.dataset.inlineLabel = '';

                const currentType = targetRow.querySelector('[data-question-type]').value;
                const allowedTypes = ['text', 'textarea', 'date'];
                const typeOptions = allowedTypes.map(value => ({ value, label: typeLabels[value] }));
                if (!allowedTypes.includes(currentType)) {
                    typeOptions.push({ value: currentType, label: typeLabels[currentType] || currentType });
                }
                const type = selectControl(typeOptions, currentType, followupLabels.type);
                type.dataset.inlineType = '';

                const placeholder = document.createElement('input');
                placeholder.className = 'form-control';
                placeholder.type = 'text';
                placeholder.maxLength = 250;
                placeholder.value = placeholderField(targetRow).value;
                placeholder.placeholder = followupLabels.placeholderExample;
                placeholder.dataset.inlinePlaceholder = '';

                const required = document.createElement('input');
                required.type = 'checkbox';
                required.checked = targetRow.querySelector('input[type="checkbox"][name$="[required]"]').checked;
                required.dataset.inlineRequired = '';
                const requiredLabel = document.createElement('label');
                requiredLabel.className = 'consultation-followup-rule__required';
                requiredLabel.appendChild(required);
                requiredLabel.append(document.createTextNode(followupLabels.required));

                const remove = document.createElement('button');
                remove.className = 'btn btn-default text-red consultation-followup-rule__remove';
                remove.type = 'button';
                remove.dataset.removeFollowup = '';
                remove.setAttribute('aria-label', followupLabels.remove);
                remove.innerHTML = '<i class="fa fa-trash" aria-hidden="true"></i>';

                const badge = document.createElement('span');
                badge.className = 'consultation-followup-rule__badge';
                badge.innerHTML = '<i class="fa fa-eye" aria-hidden="true"></i>';
                badge.append(document.createTextNode(followupLabels.badge));

                const grid = document.createElement('div');
                grid.className = 'consultation-followup-rule__grid';
                grid.appendChild(fieldGroup(followupLabels.answer, trigger));
                grid.appendChild(fieldGroup(followupLabels.question, label));
                grid.appendChild(fieldGroup(followupLabels.type, type));
                grid.appendChild(fieldGroup(followupLabels.placeholder, placeholder, 'consultation-followup-rule__placeholder'));

                const footer = document.createElement('div');
                footer.className = 'consultation-followup-rule__footer';
                footer.appendChild(requiredLabel);
                footer.appendChild(remove);

                rule.className = 'consultation-followup-rule';
                rule.dataset.targetKey = targetKey;
                rule.appendChild(badge);
                rule.appendChild(grid);
                rule.appendChild(footer);
                sourceRow.querySelector('[data-followup-rules]').appendChild(rule);
            }

            function renderFollowupBuilders(rows) {
                const rowsByKey = new Map(rows.map(row => [questionKey(row), row]));

                rows.forEach((row) => {
                    row.hidden = false;
                    delete row.dataset.inlineFollowupTarget;
                    row.querySelector('[data-followup-rules]').replaceChildren();
                    row.querySelector('[data-followup-logic]').classList.remove('has-rules');
                });

                rows.forEach((targetRow, targetIndex) => {
                    const condition = conditionFields(targetRow);
                    if (condition.enabled.value !== '1') return;

                    const sourceRow = rowsByKey.get(condition.source.value);
                    const sourceIndex = rows.indexOf(sourceRow);
                    const valid = sourceRow
                        && sourceIndex < targetIndex
                        && sourceRow.querySelector('[data-question-type]').value !== 'section'
                        && targetRow.querySelector('[data-question-type]').value !== 'section';

                    if (!valid) {
                        clearCondition(targetRow);
                        return;
                    }

                    targetRow.hidden = true;
                    targetRow.dataset.inlineFollowupTarget = 'true';
                    appendFollowupEditor(sourceRow, targetRow);
                    sourceRow.querySelector('[data-followup-logic]').classList.add('has-rules');
                });

                rows.forEach((row) => {
                    const type = row.querySelector('[data-question-type]').value;
                    const section = row.querySelector('[data-followup-logic]');
                    const addButton = row.querySelector('[data-add-followup]');
                    const hasRules = section.classList.contains('has-rules');
                    const isTarget = row.dataset.inlineFollowupTarget === 'true';

                    section.hidden = type === 'section' || isTarget;
                    addButton.disabled = hasRules;
                    addButton.hidden = hasRules;
                });
            }

            function refreshRows() {
                const rows = [...container.querySelectorAll('[data-question-row]')];
                empty.hidden = rows.some(row => conditionFields(row).enabled.value !== '1');
                rows.forEach((row, index) => {
                    row.querySelectorAll('[name]').forEach((field) => field.name = field.name.replace(/questions\[[^\]]+\]/, `questions[${index}]`));
                    const type = row.querySelector('[data-question-type]').value;
                    row.querySelector('[data-question-options]').hidden = !['select', 'checkbox', 'body_map'].includes(type);
                    row.querySelector('[data-question-required]').hidden = type === 'section';
                    row.classList.toggle('is-section', type === 'section');
                    row.classList.toggle('is-body-map', type === 'body_map');
                    updateBodyMap(row);
                });
                renderFollowupBuilders(rows);

                let visibleNumber = 0;
                rows.forEach((row) => {
                    if (row.dataset.inlineFollowupTarget === 'true') return;
                    visibleNumber++;
                    row.querySelector('[data-question-number]').textContent = visibleNumber;
                });
            }

            let refreshTimer = null;

            function scheduleRefreshRows() {
                window.clearTimeout(refreshTimer);
                refreshTimer = window.setTimeout(refreshRows, 180);
            }

            function createQuestionRow(afterRow = null) {
                const index = container.querySelectorAll('[data-question-row]').length;
                const key = `q_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 7)}`;
                const html = template.innerHTML.replaceAll('__INDEX__', index).replaceAll('__KEY__', key);

                if (afterRow) {
                    afterRow.insertAdjacentHTML('afterend', html);
                } else {
                    container.insertAdjacentHTML('beforeend', html);
                }

                return [...container.querySelectorAll('[data-question-row]')]
                    .find(row => questionKey(row) === key);
            }

            function childRows(sourceRow) {
                const sourceKey = questionKey(sourceRow);

                return [...container.querySelectorAll('[data-question-row]')].filter(row => {
                    const condition = conditionFields(row);
                    return condition.enabled.value === '1' && condition.source.value === sourceKey;
                });
            }

            function moveQuestionGroup(row, direction) {
                const rows = [...container.querySelectorAll('[data-question-row]')];
                const parents = rows.filter(candidate => conditionFields(candidate).enabled.value !== '1');
                const currentIndex = parents.indexOf(row);
                const destination = currentIndex + direction;

                if (currentIndex < 0 || destination < 0 || destination >= parents.length) return;

                const groups = parents.map(parent => [parent, ...childRows(parent)]);
                [groups[currentIndex], groups[destination]] = [groups[destination], groups[currentIndex]];
                groups.flat().forEach(question => container.appendChild(question));
                refreshRows();
            }

            document.getElementById('add-consultation-question').addEventListener('click', function () {
                const row = createQuestionRow();
                refreshRows();
                row.querySelector('input[type=text]').focus();
            });

            container.addEventListener('click', function (event) {
                const row = event.target.closest('[data-question-row]');
                if (!row) return;

                if (event.target.closest('[data-add-followup]')) {
                    const existingChildren = childRows(row);
                    if (existingChildren.length) return;

                    const targetRow = createQuestionRow(row);
                    const targetType = targetRow.querySelector('[data-question-type]');
                    const required = targetRow.querySelector('input[type="checkbox"][name$="[required]"]');
                    const choices = answerChoices(row);
                    const defaultValue = choices[0]?.value || '';

                    targetType.value = 'textarea';
                    required.checked = true;
                    setCondition(targetRow, questionKey(row), conditionOperatorFor(row), defaultValue);
                    refreshRows();

                    const editor = row.querySelector(`.consultation-followup-rule[data-target-key="${questionKey(targetRow)}"]`);
                    editor?.querySelector('[data-inline-label]')?.focus();
                    return;
                }

                const followupRule = event.target.closest('.consultation-followup-rule');
                if (event.target.closest('[data-remove-followup]') && followupRule) {
                    const targetRow = [...container.querySelectorAll('[data-question-row]')]
                        .find(candidate => questionKey(candidate) === followupRule.dataset.targetKey);
                    if (targetRow) targetRow.remove();
                    refreshRows();
                    return;
                }

                if (event.target.closest('[data-remove-question]')) {
                    childRows(row).forEach(child => child.remove());
                    row.remove();
                    refreshRows();
                    return;
                }

                if (event.target.closest('[data-move-up]')) {
                    moveQuestionGroup(row, -1);
                    return;
                }

                if (event.target.closest('[data-move-down]')) {
                    moveQuestionGroup(row, 1);
                }
            });

            container.addEventListener('change', function (event) {
                if (event.target.matches('[data-question-type]')) {
                    refreshRows();
                    return;
                }

                const rule = event.target.closest('.consultation-followup-rule');
                if (!rule) return;

                const targetRow = [...container.querySelectorAll('[data-question-row]')]
                    .find(row => questionKey(row) === rule.dataset.targetKey);
                if (!targetRow) return;

                if (event.target.matches('[data-inline-trigger]')) {
                    conditionFields(targetRow).value.value = event.target.value;
                }

                if (event.target.matches('[data-inline-type]')) {
                    targetRow.querySelector('[data-question-type]').value = event.target.value;
                    refreshRows();
                }

                if (event.target.matches('[data-inline-required]')) {
                    targetRow.querySelector('input[type="checkbox"][name$="[required]"]').checked = event.target.checked;
                }
            });

            container.addEventListener('input', function (event) {
                const row = event.target.closest('[data-question-row]');
                if (row) updateBodyMap(row);
                if (event.target.matches('[data-question-options-input]')) scheduleRefreshRows();

                if (event.target.matches('[data-inline-trigger], [data-inline-label], [data-inline-placeholder]')) {
                    const rule = event.target.closest('.consultation-followup-rule');
                    const targetRow = [...container.querySelectorAll('[data-question-row]')]
                        .find(candidate => questionKey(candidate) === rule.dataset.targetKey);
                    if (!targetRow) return;

                    if (event.target.matches('[data-inline-trigger]')) {
                        conditionFields(targetRow).value.value = event.target.value;
                    } else if (event.target.matches('[data-inline-label]')) {
                        targetRow.querySelector('input[name$="[label]"]').value = event.target.value;
                    } else {
                        placeholderField(targetRow).value = event.target.value;
                    }
                }
            });

            refreshRows();
        })();
    </script>
@endpush
