<div class="report-filter-zone {{ $class ?? '' }}">
    <div class="report-filter-zone__head">
        <span class="report-filter-zone__icon" aria-hidden="true">
            <i class="fa {{ $icon ?? 'fa-sliders' }}"></i>
        </span>
        <div class="report-filter-zone__titles">
            <h5 class="report-filter-zone__title">{{ $title }}</h5>
            @if (!empty($hint))
                <p class="report-filter-zone__hint">{{ $hint }}</p>
            @endif
        </div>
    </div>

    <div class="report-filter-zone__grid">
        {{ $slot }}
    </div>
</div>
