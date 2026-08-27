@php
    $messageName = $messageName ?? '';
    $rows = $rows ?? 5;
    $defaultTemplate = config('setting.whatsapp_notifications.' . $messageName);
    $placeholder = is_string($defaultTemplate)
        ? \Illuminate\Support\Str::limit(str_replace(["\r\n", "\n", "\r"], ' ', $defaultTemplate), 140)
        : '';
    $showDefaultPreview = $showDefaultPreview ?? true;
    $currentTemplate = $settings[$messageName] ?? null;
    $previewTemplate = is_string($currentTemplate) && trim($currentTemplate) !== ''
        ? $currentTemplate
        : $defaultTemplate;
@endphp

<div class="st-wa-template">
    <div class="st-wa-template__editor">
        {{ Form::textarea($messageName, trans('setting::attributes.' . $messageName), $errors, $settings, [
            'rows' => $rows,
            'class' => 'form-control st-wa-template__textarea',
            'placeholder' => $placeholder,
            'data-wa-preview' => 'true',
            'data-wa-preview-default' => $previewTemplate,
        ]) }}
        <p class="help-block text-muted st-wa-template__hint">{{ $hint ?? trans('setting::settings.sms.template_hint') }}</p>
        @if ($showDefaultPreview && is_string($defaultTemplate) && trim($defaultTemplate) !== '')
            <details class="st-wa-template__default">
                <summary>{{ trans('setting::settings.sms.default_template_preview') }}</summary>
                <pre class="st-wa-template__default-body">{{ $defaultTemplate }}</pre>
            </details>
        @endif
    </div>

    <aside class="st-wa-template__preview" aria-label="{{ trans('setting::settings.sms.preview_title') }}">
        <div class="st-wa-template__preview-head">
            <span class="st-wa-template__preview-icon"><i class="fa fa-whatsapp" aria-hidden="true"></i></span>
            <span>
                <strong>{{ trans('setting::settings.sms.preview_title') }}</strong>
                <small>{{ trans('setting::settings.sms.preview_recipient') }}</small>
            </span>
            <span class="st-wa-template__preview-status"><i class="fa fa-check" aria-hidden="true"></i></span>
        </div>
        <div class="st-wa-template__preview-body" data-wa-preview-output>{{ $previewTemplate }}</div>
        <div class="st-wa-template__preview-time">{{ trans('setting::settings.sms.preview_now') }} <span>✓✓</span></div>
    </aside>
</div>
