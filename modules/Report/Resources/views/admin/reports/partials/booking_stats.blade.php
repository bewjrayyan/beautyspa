@php
    $stats = $bookingStats ?? [];
@endphp

<div class="report-bookings-hero">
    <div class="report-bookings-hero-text">
        <h3 class="report-bookings-hero-title">{{ trans('report::admin.bookings.title') }}</h3>
        <p class="report-bookings-hero-subtitle">{{ trans('report::admin.bookings.subtitle') }}</p>
    </div>
</div>

<div class="fc-saas-stats fc-saas-stats--4">
    @include('admin::partials.fc_saas_stat', [
        'variant' => 'sky',
        'icon' => 'fa-calendar',
        'label' => trans('report::admin.bookings.stats.today'),
        'value' => number_format($stats['today'] ?? 0),
        'hint' => trans('report::admin.bookings.stats.today_hint'),
    ])

    @include('admin::partials.fc_saas_stat', [
        'variant' => 'violet',
        'icon' => 'fa-clock-o',
        'label' => trans('report::admin.bookings.stats.upcoming'),
        'value' => number_format($stats['upcoming'] ?? 0),
        'hint' => trans('report::admin.bookings.stats.upcoming_hint'),
    ])

    @include('admin::partials.fc_saas_stat', [
        'variant' => 'mint',
        'icon' => 'fa-check-circle',
        'label' => trans('report::admin.bookings.stats.completed'),
        'value' => number_format($stats['completed'] ?? 0),
        'hint' => trans('report::admin.bookings.stats.completed_hint'),
    ])

    @include('admin::partials.fc_saas_stat', [
        'variant' => 'peach',
        'icon' => 'fa-line-chart',
        'label' => trans('report::admin.bookings.stats.total_sales'),
        'value' => ($stats['totalSales'] ?? null)?->format() ?? '0.00',
        'hint' => trans('report::admin.bookings.stats.total_sales_hint'),
    ])
</div>
