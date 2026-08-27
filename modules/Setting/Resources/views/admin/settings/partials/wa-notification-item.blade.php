@php
    $enabledName = $enabledName ?? '';
    $enabledLabel = $enabledLabel ?? '';
    $hint = $hint ?? null;
    $badge = $badge ?? null;
    $hasFields = isset($slot) && trim((string) $slot) !== '';
@endphp

<div class="st-wa-item{{ $hasFields ? ' st-wa-item--with-fields' : '' }}">
    <div class="st-wa-item__meta">
        <div class="st-wa-item__toggle">
            {{ Form::checkbox($enabledName, trans('setting::attributes.' . $enabledName), $enabledLabel, $errors, $settings) }}
            @if (! empty($badge))
                <span class="st-wa-badge st-wa-badge--{{ $badge['type'] ?? 'muted' }}">{{ $badge['text'] }}</span>
            @endif
        </div>

        @if (! empty($hint))
            <p class="st-wa-item__hint">{{ $hint }}</p>
        @endif
    </div>

    @if ($hasFields)
        <div class="st-wa-item__fields">
            {{ $slot }}
        </div>
    @endif
</div>
