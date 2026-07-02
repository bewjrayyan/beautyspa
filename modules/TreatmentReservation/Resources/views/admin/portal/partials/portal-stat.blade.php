<article @class([
    'tr-portal-kpi',
    'tr-portal-kpi--' . ($tone ?? 'neutral'),
    'tr-portal-kpi--featured' => ! empty($featured),
])>
    <div class="tr-portal-kpi__head">
        <span class="tr-portal-kpi__label">{{ $label }}</span>
        <span class="tr-portal-kpi__icon" aria-hidden="true">
            <i class="fa {{ $icon }}"></i>
        </span>
    </div>

    <p class="tr-portal-kpi__value">{{ $value }}</p>

    @if (! empty($hint))
        <p class="tr-portal-kpi__hint">{{ $hint }}</p>
    @endif
</article>
