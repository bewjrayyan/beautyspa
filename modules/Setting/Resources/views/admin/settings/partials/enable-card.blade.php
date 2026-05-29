@php
    $name = $name ?? '';
    $label = $label ?? '';
    $checkboxLabel = $checkboxLabel ?? trans('admin::admin.form.enable');
@endphp

<div class="st-enable-card">
    {{ Form::checkbox($name, ' ', $checkboxLabel, $errors, $settings, ['labelCol' => 0]) }}
    @if (! empty($label))
        <p class="st-enable-card__hint">{{ $label }}</p>
    @endif
</div>
