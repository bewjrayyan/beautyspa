<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $submission->form_title }}</title>
    <style>
        @page { margin: 26px 30px 38px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #2b2228; font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.38; }
        .accent { height: 6px; margin: -26px -30px 14px; background: #b50863; }
        .header { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .brand { color: #8f064e; font-size: 11px; font-weight: bold; letter-spacing: .7px; text-transform: uppercase; }
        h1 { margin: 4px 0 3px; color: #241a21; font-size: 19px; line-height: 1.12; }
        .subtitle { margin: 0; color: #756b72; font-size: 8px; }
        .status { display: inline-block; padding: 4px 8px; color: #087447; background: #e4f7ee; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .meta { width: 100%; margin-bottom: 10px; border: 1px solid #e8dfe4; border-collapse: collapse; }
        .meta td { width: 33.333%; padding: 6px 8px; border: 1px solid #e8dfe4; vertical-align: top; }
        .meta-label { display: block; margin-bottom: 2px; color: #8a7f86; font-size: 8px; font-weight: bold; letter-spacing: .5px; text-transform: uppercase; }
        .intro { margin: 5px 0 8px; color: #62575e; font-size: 8px; }
        .section-title { margin: 9px 0 5px; padding-bottom: 4px; border-bottom: 2px solid #b50863; color: #6f073f; font-size: 11px; }
        .section-divider { margin: 7px 0 3px; padding: 5px 7px; color: #85064a; background: #fbedf5; font-size: 9px; font-weight: bold; page-break-after: avoid; }
        .section-divider small { display: block; margin-top: 1px; color: #74616c; font-size: 7px; font-weight: normal; }
        .answer-grid { width: calc(100% + 8px); margin: -3px -4px 2px; border-collapse: separate; border-spacing: 4px 3px; table-layout: fixed; }
        .answer-cell { width: 50%; padding: 6px 8px; border: 1px solid #ece5e9; vertical-align: top; page-break-inside: avoid; }
        .answer-cell.is-empty { border-color: transparent; }
        .question { position: relative; min-height: 17px; margin-bottom: 3px; padding-left: 18px; color: #51464d; font-size: 8px; font-weight: bold; line-height: 1.3; }
        .question > b { position: absolute; top: 0; left: 0; display: block; width: 14px; height: 14px; padding-top: 1px; border-radius: 50%; color: #fff; background: #b50863; font-size: 7px; text-align: center; }
        .question > span,.question > small { display: block; }
        .question > small { margin-top: 1px; color: #867780; font-size: 7px; font-weight: normal; }
        .response { color: #241a21; font-size: 8.5px; line-height: 1.35; }
        .body-map-answer { margin: 4px 0 5px; padding: 7px 9px; border: 1px solid #decfd8; background: #fffafd; page-break-inside: avoid; }
        .body-map-heading { margin-bottom: 4px; }
        .body-map-labels { width: 100%; white-space: nowrap; }
        .body-map-labels span { display: inline-block; width: 24%; margin-bottom: 3px; color: #8f7c87; font-size: 6px; font-weight: bold; text-align: center; text-transform: uppercase; }
        .body-map-image { display: block; width: 100%; height: auto; margin: 0 auto; }
        .body-map-help { margin-top: 3px; color: #786b73; font-size: 6px; text-align: center; }
        .body-map-selected { margin-top: 5px; text-align: left; }
        .body-map-selected span { display: inline-block; margin: 2px 3px 0 0; padding: 3px 6px; border: 1px solid #d7afc4; border-radius: 8px; color: #85064a; background: #fff; font-size: 7px; font-weight: bold; }
        .body-map-selected b { display: inline-block; margin-right: 3px; color: #fff; background: #b50863; font-size: 6px; }
        .closing-table { width: calc(100% + 8px); margin: 5px -4px 0; border-collapse: separate; border-spacing: 4px 0; table-layout: fixed; page-break-inside: avoid; }
        .closing-table td { width: 50%; padding: 8px; vertical-align: top; }
        .consent { border-left: 4px solid #b50863; background: #fff4fa; }
        .consent strong { display: block; margin-bottom: 3px; color: #8f064e; }
        .legal-record { margin: 5px 0 0; padding: 0 0 0 14px; color: #685b63; font-size: 7px; }
        .legal-record li { margin-bottom: 2px; }
        .signature-box { border: 1px solid #e7dee3; }
        .signature-box img { display: block; width: 180px; max-height: 62px; }
        .signature-meta { margin-top: 3px; color: #81767d; font-size: 7px; }
        .footer { position: fixed; right: 0; bottom: -25px; left: 0; padding-top: 6px; border-top: 1px solid #e7dfe4; color: #92878e; font-size: 7px; text-align: center; }
    </style>
</head>
<body>
    <div class="accent"></div>
    <table class="header"><tr><td><div class="brand">{{ setting('store_name') ?: config('app.name') }}</div><h1>{{ $submission->form_title }}</h1><p class="subtitle">{{ $consultationContext['treatment_name'] ?? '—' }}</p></td><td style="text-align:right"><span class="status">{{ trans('account::consultation.complete') }}</span></td></tr></table>
    <table class="meta">
        <tr>
            <td><span class="meta-label">{{ trans('account::consultation.pdf.customer') }}</span>{{ $submission->customer_name ?: $submission->user?->full_name }}</td>
            <td><span class="meta-label">{{ trans('account::consultation.treatment') }}</span>{{ $consultationContext['treatment_name'] ?? '—' }}</td>
            <td><span class="meta-label">{{ trans('account::consultation.pdf.date_version') }}</span>{{ $submission->submitted_at?->format('d M Y, H:i') }} · v{{ $submission->template_version }}</td>
        </tr>
        <tr>
            <td><span class="meta-label">{{ trans('account::consultation.pdf.contact') }}</span>{{ $submission->customer_email ?: $submission->user?->email }}@if($submission->customer_phone ?: $submission->user?->phone) · {{ $submission->customer_phone ?: $submission->user?->phone }}@endif</td>
            <td><span class="meta-label">Beautician</span>{{ $consultationContext['beautician_name'] ?? '—' }}</td>
            <td><span class="meta-label">{{ trans('account::consultation.pdf.order') }}</span>{{ $submission->order_id ? '#'.$submission->order_id : '—' }}</td>
        </tr>
    </table>
    @if ($submission->form_intro)<p class="intro">{{ $submission->form_intro }}</p>@endif
    <h2 class="section-title">{{ trans('account::consultation.answers') }}</h2>
    @php
        $answerNumber = 0;
        $sections = [];
        $currentSection = ['key' => null, 'label' => null, 'items' => []];
        $conditionalVisibility = app(\Modules\Account\Services\ConsultationConditionEvaluator::class)
            ->visibilityMap($submission->questions_snapshot ?: [], $submission->answers ?: []);

        foreach ($submission->questions_snapshot as $question) {
            if (($question['type'] ?? null) === 'section') {
                if (count($currentSection['items'])) {
                    $sections[] = $currentSection;
                }

                $currentSection = ['key' => $question['key'] ?? null, 'label' => $question['label'], 'items' => []];
                continue;
            }

            if (! ($conditionalVisibility[$question['key'] ?? ''] ?? true)) {
                continue;
            }

            $answerNumber++;
            $currentSection['items'][] = ['number' => $answerNumber, 'question' => $question];
        }

        if (count($currentSection['items'])) {
            $sections[] = $currentSection;
        }
    @endphp
    @foreach ($sections as $section)
        @if ($section['label'])
            @php $sectionLabel = \Modules\Account\Support\ConsultationQuestionLabel::parts(['key' => $section['key'] ?? '', 'label' => $section['label']]); @endphp
            <div class="section-divider">
                {{ $sectionLabel['primary'] }}
                @if ($sectionLabel['english'])<small lang="en">{{ $sectionLabel['english'] }}</small>@endif
            </div>
        @endif
        @php
            $bodyMapOffset = collect($section['items'])->search(
                fn (array $item): bool => ($item['question']['type'] ?? null) === 'body_map'
            );
            $beforeBodyMap = $bodyMapOffset === false ? $section['items'] : array_slice($section['items'], 0, $bodyMapOffset);
            $afterBodyMap = $bodyMapOffset === false ? [] : array_slice($section['items'], $bodyMapOffset + 1);
        @endphp

        @include('account::consultations.partials.answer-grid', ['items' => $beforeBodyMap])

        @if ($bodyMapOffset !== false)
            @php
                $bodyMapItem = $section['items'][$bodyMapOffset];
                $question = $bodyMapItem['question'];
                $answer = data_get($submission->answers, $question['key']);
                $bodyMapSvg = \Modules\Account\Support\ConsultationBodyMap::annotatedSvgDataUri(
                    is_array($answer) ? $answer : [],
                    $question['options'] ?? []
                );
            @endphp
            <div class="body-map-answer">
                @php $bodyMapLabel = \Modules\Account\Support\ConsultationQuestionLabel::parts($question); @endphp
                <div class="question body-map-heading">
                    <b>{{ $bodyMapItem['number'] }}</b>
                    <span>{{ $bodyMapLabel['primary'] }}</span>
                    @if ($bodyMapLabel['english'])<small lang="en">{{ $bodyMapLabel['english'] }}</small>@endif
                </div>
                <div class="body-map-labels">
                    <span>{{ trans('account::consultation.medical.male_front') }}</span>
                    <span>{{ trans('account::consultation.medical.male_back') }}</span>
                    <span>{{ trans('account::consultation.medical.female_front') }}</span>
                    <span>{{ trans('account::consultation.medical.female_back') }}</span>
                </div>
                <img class="body-map-image" src="{{ $bodyMapSvg }}" alt="{{ trans('account::consultation.medical.body_map_alt') }}">
                <div class="body-map-help">{{ trans('account::consultation.medical.marker_help') }}</div>
                <div class="body-map-selected">
                    @if (is_array($answer) && count($answer))
                        @foreach ($answer as $area)
                            @php $areaIndex = array_search($area, $question['options'] ?? [], true); @endphp
                            <span><b>{{ $areaIndex === false ? '?' : $areaIndex + 1 }}</b>{{ $area }}</span>
                        @endforeach
                    @else
                        <span>—</span>
                    @endif
                </div>
            </div>

            @include('account::consultations.partials.answer-grid', ['items' => $afterBodyMap])
        @endif
    @endforeach
    <table class="closing-table"><tr>
        <td class="consent">
            <strong>{{ trans('account::consultation.pdf.consent_accepted') }}</strong>{{ $submission->consent_text }}
            @if (count($submission->legal_documents_snapshot ?? []))
                <strong style="margin-top:6px">{{ trans('account::consultation.pdf.legal_record') }}</strong>
                <ul class="legal-record">
                    @foreach ($submission->legal_documents_snapshot as $document)
                        <li>{{ $document['title'] ?? $document['slug'] }} · {{ $document['version'] ?? '—' }} · {{ substr($document['content_hash'] ?? '', 0, 12) }}</li>
                    @endforeach
                </ul>
            @endif
        </td>
        <td class="signature-box"><span class="meta-label">{{ trans('account::consultation.signature') }}</span><img src="{{ $signatureDataUri }}" alt="{{ trans('account::consultation.signature') }}"><div class="signature-meta">{{ trans('account::consultation.pdf.signed_by', ['name' => $submission->user?->full_name, 'date' => $submission->submitted_at?->format('d M Y, H:i')]) }}</div></td>
    </tr></table>
    <div class="footer">{{ setting('store_name') ?: config('app.name') }} · {{ trans('account::consultation.pdf.document_reference', ['id' => $submission->id]) }}</div>
</body>
</html>
