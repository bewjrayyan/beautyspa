<article class="tr-stat-card tr-stat-card--{{ $variant }}">
    <div class="tr-stat-card__head">
        @if (! empty($icon))
            <span class="tr-stat-card__icon" aria-hidden="true">
                <i class="fa {{ $icon }}"></i>
            </span>
        @endif
        <span class="tr-stat-card__label">{{ $label }}</span>
    </div>
    <p class="tr-stat-card__value">{{ $value }}</p>
    @if (! empty($hint))
        <p class="tr-stat-card__desc">{{ $hint }}</p>
    @endif
</article>
