@php
    $messageName = $messageName ?? '';
    $rows = $rows ?? 5;
    $defaultTemplate = config('setting.whatsapp_notifications.' . $messageName);
    $placeholder = is_string($defaultTemplate)
        ? \Illuminate\Support\Str::limit(str_replace(["\r\n", "\n", "\r"], ' ', $defaultTemplate), 140)
        : '';
@endphp

<div class="st-wa-template">
    {{ Form::textarea($messageName, trans('setting::attributes.' . $messageName), $errors, $settings, [
        'rows' => $rows,
        'class' => 'form-control st-wa-template__textarea',
        'placeholder' => $placeholder,
    ]) }}
    <p class="help-block text-muted st-wa-template__hint">{{ $hint ?? trans('setting::settings.sms.template_hint') }}</p>
</div>
