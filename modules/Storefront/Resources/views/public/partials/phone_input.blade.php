@php
    $inputId = $id ?? $name;
    $inputClass = trim(($class ?? 'form-control') . ' modern-phone-input');
@endphp

<input
    type="tel"
    name="{{ $name }}"
    id="{{ $inputId }}"
    value="{{ old($name, $value ?? '') }}"
    class="{{ $inputClass }}"
    autocomplete="{{ $autocomplete ?? 'tel' }}"
    @if (! empty($required)) required @endif
    @if (! empty($placeholder)) placeholder="{{ $placeholder }}" @endif
    {!! $extraAttributes ?? '' !!}
>
