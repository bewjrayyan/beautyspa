@hasAccess('admin.orders.index')
    <div class="dashboard-hero">
        <div class="dashboard-hero__header">
            <div class="dashboard-hero__greeting">
                <h2 class="dashboard-hero__title">
                    {{ trans('admin::dashboard.welcome_back') }}, {{ auth()->user()->first_name ?? 'Admin' }}
                </h2>
                <p class="dashboard-hero__subtitle">{{ trans('admin::dashboard.hero_subtitle') }}</p>
            </div>
            <div class="dashboard-hero__actions">
                <span class="dashboard-hero__date">
                    <i class="fa fa-calendar-o" aria-hidden="true"></i>
                    {{ now()->translatedFormat('l, F j, Y') }}
                </span>
            </div>
        </div>

        <div class="dashboard-hero__stats">
            @include('admin::partials.fc_saas_stat', [
                'variant' => 'hero',
                'icon' => 'fa-shopping-cart',
                'label' => trans('admin::dashboard.total_sales'),
                'value' => $totalSales->KMBTFormat(),
                'valueTitle' => $totalSales->format(),
                'hint' => null,
                'trend' => $trends['sales'] ?? null,
                'sparkline' => null,
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'hero',
                'icon' => 'fa-calendar',
                'label' => trans('admin::dashboard.this_month_sales'),
                'value' => $thisMonthSales->KMBTFormat(),
                'valueTitle' => $thisMonthSales->format(),
                'hint' => null,
                'trend' => $trends['orders'] ?? null,
                'sparkline' => null,
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'hero',
                'icon' => 'fa-money',
                'label' => trans('admin::dashboard.pending_payment_orders'),
                'value' => number_format($pendingPaymentCount),
                'hint' => null,
                'trend' => $trends['pending'] ?? null,
                'sparkline' => null,
                'url' => route('admin.orders.index', ['payment_status' => 'pending']),
                'cta' => trans('admin::dashboard.view_all'),
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'hero',
                'icon' => 'fa-calendar-check-o',
                'label' => trans('admin::dashboard.today_appointments'),
                'value' => number_format($todayAppointmentsCount),
                'hint' => null,
                'sparkline' => null,
                'url' => is_module_enabled('TreatmentReservation')
                    ? route('admin.treatment_reservations.portal.job_sheet')
                    : null,
                'cta' => is_module_enabled('TreatmentReservation')
                    ? trans('admin::dashboard.open')
                    : null,
            ])
        </div>
    </div>
@endHasAccess
