@hasAccess('admin.orders.index')
    <div class="fc-saas-stats fc-saas-stats--5">
        @include('admin::partials.fc_saas_stat', [
            'variant' => 'sky',
            'icon' => 'fa-line-chart',
            'label' => trans('admin::dashboard.total_sales'),
            'value' => $totalSales->KMBTFormat(),
            'valueTitle' => $totalSales->format(),
            'hint' => trans('admin::dashboard.hints.total_sales'),
        ])

        @include('admin::partials.fc_saas_stat', [
            'variant' => 'slate',
            'icon' => 'fa-calendar',
            'label' => trans('admin::dashboard.this_month_sales'),
            'value' => $thisMonthSales->KMBTFormat(),
            'valueTitle' => $thisMonthSales->format(),
            'hint' => trans('admin::dashboard.hints.this_month_sales'),
        ])

        @include('admin::partials.fc_saas_stat', [
            'variant' => 'amber',
            'icon' => 'fa-hourglass-half',
            'label' => trans('admin::dashboard.pending_payment_orders'),
            'value' => number_format($pendingPaymentCount),
            'hint' => trans('admin::dashboard.hints.pending_payment_orders'),
            'url' => route('admin.orders.index', ['payment_status' => 'pending']),
            'cta' => trans('admin::dashboard.view_all'),
        ])

        @include('admin::partials.fc_saas_stat', [
            'variant' => 'mint',
            'icon' => 'fa-calendar-check-o',
            'label' => trans('admin::dashboard.today_appointments'),
            'value' => number_format($todayAppointmentsCount),
            'hint' => trans('admin::dashboard.hints.today_appointments'),
            'url' => is_module_enabled('TreatmentReservation')
                ? route('admin.treatment_reservations.calendar')
                : null,
            'cta' => is_module_enabled('TreatmentReservation')
                ? trans('admin::dashboard.open')
                : null,
        ])

        @if ($topStatsShowLoyalty ?? false)
            @include('admin::partials.fc_saas_stat', [
                'variant' => 'gold',
                'icon' => 'fa-star',
                'label' => trans('admin::dashboard.loyalty_members'),
                'value' => number_format($loyaltyMembersTotal),
                'hint' => trans('admin::dashboard.hints.loyalty_members', [
                    'active' => number_format($loyaltyMembersWithBalance),
                ]),
                'url' => $loyaltyMembersUrl,
                'cta' => trans('admin::dashboard.open'),
            ])
        @else
            @include('admin::partials.fc_saas_stat', [
                'variant' => 'peach',
                'icon' => 'fa-users',
                'label' => trans('admin::dashboard.total_customers'),
                'value' => number_format($totalCustomers),
                'hint' => trans('admin::dashboard.hints.total_customers'),
                'url' => route('admin.users.index'),
                'cta' => trans('admin::dashboard.open'),
            ])
        @endif
    </div>
@endHasAccess
