@if (! empty($url))
    <a href="{{ $url }}" class="fc-saas-stat-link">
@endif
<article class="fc-saas-stat fc-saas-stat--{{ $variant }}">
    <div class="fc-saas-stat-icon" aria-hidden="true">
        <i class="fa {{ $icon }}"></i>
    </div>
    <div class="fc-saas-stat-body">
        <span class="fc-saas-stat-label">{{ $label }}</span>
        <span class="fc-saas-stat-value" @if (! empty($valueTitle)) title="{{ $valueTitle }}" @endif>{{ $value }}</span>
        @if (! empty($hint))
            <span class="fc-saas-stat-hint">{{ $hint }}</span>
        @endif
    </div>
    @if (! empty($cta))
        <span class="fc-saas-stat-cta">
            {{ $cta }}
            <i class="fa fa-arrow-right" aria-hidden="true"></i>
        </span>
    @endif
</article>
@if (! empty($url))
    </a>
@endif
