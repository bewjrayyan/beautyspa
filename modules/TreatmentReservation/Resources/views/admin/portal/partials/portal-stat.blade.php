<article @class([
    'tr-stat-card',
    'tr-stat-card--' . ($tone ?? 'neutral'),
    'tr-stat-card--featured' => ! empty($featured),
])>
    <div class="tr-stat-card__accent" aria-hidden="true"></div>
    <div class="tr-stat-card__icon" aria-hidden="true">
        <i class="fa {{ $icon }}"></i>
    </div>
    <div class="tr-stat-card__body">
        <span class="tr-stat-card__value">{{ $value }}</span>
        <span class="tr-stat-card__label">{{ $label }}</span>
        @if (! empty($hint))
            <span class="tr-stat-card__hint">{{ $hint }}</span>
        @endif
    </div>
</article>
