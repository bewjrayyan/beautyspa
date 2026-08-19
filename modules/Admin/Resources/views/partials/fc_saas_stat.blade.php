@if (! empty($url))
    <a href="{{ $url }}" class="fc-saas-stat-link">
@endif
<article class="fc-saas-stat fc-saas-stat--{{ $variant }}">
    <header class="fc-saas-stat-header">
        <div class="fc-saas-stat-icon" aria-hidden="true">
            <i class="fa {{ $icon }}"></i>
        </div>
        <span class="fc-saas-stat-label">{{ $label }}</span>
    </header>
    <div class="fc-saas-stat-body">
        <span class="fc-saas-stat-value" @if (! empty($valueTitle)) title="{{ $valueTitle }}" @endif>{{ $value }}</span>
    </div>
    <footer class="fc-saas-stat-footer">
        @if (! empty($trend))
            <span class="fc-saas-stat-trend fc-saas-stat-trend--{{ $trend['direction'] }}">
                <i class="fa fa-arrow-{{ $trend['direction'] }}" aria-hidden="true"></i>
                {{ abs($trend['pct']) }}%
                <span class="fc-saas-stat-trend-label">{{ trans('admin::dashboard.vs_last_week') }}</span>
            </span>
        @elseif (! empty($hint))
            <span class="fc-saas-stat-hint">{{ $hint }}</span>
        @endif
        @if (! empty($sparkline))
            <div class="fc-saas-stat-sparkline" aria-hidden="true">
                <svg viewBox="0 0 80 32" preserveAspectRatio="none">
                    @php
                        $max = max($sparkline) ?: 1;
                        $points = [];
                        foreach ($sparkline as $i => $v) {
                            $x = round(($i / (count($sparkline) - 1)) * 80, 2);
                            $y = round(32 - ($v / $max) * 28, 2);
                            $points[] = "$x,$y";
                        }
                    @endphp
                    <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        @endif
        @if (! empty($cta))
            <span class="fc-saas-stat-cta">
                {{ $cta }}
                <i class="fa fa-arrow-right" aria-hidden="true"></i>
            </span>
        @endif
    </footer>
</article>
@if (! empty($url))
    </a>
@endif
