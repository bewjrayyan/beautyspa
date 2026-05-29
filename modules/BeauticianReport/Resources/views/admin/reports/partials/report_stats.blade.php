<div class="fc-saas-stats">
    @include('admin::partials.fc_saas_stat', [
        'variant' => 'peach',
        'icon' => 'fa-line-chart',
        'label' => trans('beauticianreport::admin.stats.treatment_sales'),
        'value' => $stats['totalSales']->format(),
        'hint' => trans('beauticianreport::admin.stats.filtered_sales_hint'),
    ])

    @include('admin::partials.fc_saas_stat', [
        'variant' => 'sky',
        'icon' => 'fa-shopping-cart',
        'label' => trans('beauticianreport::admin.stats.treatment_orders'),
        'value' => number_format($stats['totalOrders']),
        'hint' => trans('beauticianreport::admin.stats.filtered_orders_hint'),
    ])

    @include('admin::partials.fc_saas_stat', [
        'variant' => 'mint',
        'icon' => 'fa-check-circle',
        'label' => trans('beauticianreport::admin.stats.completed'),
        'value' => number_format($stats['completedOrders']),
        'hint' => trans('beauticianreport::admin.stats.filtered_completed_hint'),
    ])

    @include('admin::partials.fc_saas_stat', [
        'variant' => 'violet',
        'icon' => 'fa-cube',
        'label' => trans('beauticianreport::admin.stats.total_products'),
        'value' => number_format($stats['totalProducts']),
        'hint' => trans('beauticianreport::admin.stats.filtered_products_hint'),
    ])

    @include('admin::partials.fc_saas_stat', [
        'variant' => 'indigo',
        'icon' => 'fa-calendar-check-o',
        'label' => trans('beauticianreport::admin.stats.with_appointment'),
        'value' => number_format($stats['withAppointment']),
        'hint' => trans('beauticianreport::admin.stats.filtered_appointment_hint'),
    ])
</div>
