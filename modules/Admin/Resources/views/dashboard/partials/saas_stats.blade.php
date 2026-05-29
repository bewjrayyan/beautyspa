<div class="fc-saas-stats">
    @hasAccess('admin.orders.index')
        @include('admin::partials.fc_saas_stat', [
            'variant' => 'sky',
            'icon' => 'fa-line-chart',
            'label' => trans('admin::dashboard.total_sales'),
            'value' => $totalSales->KMBTFormat(),
            'valueTitle' => $totalSales->format(),
            'hint' => trans('admin::dashboard.hints.total_sales'),
        ])

        @include('admin::partials.fc_saas_stat', [
            'variant' => 'rose',
            'icon' => 'fa-shopping-cart',
            'label' => trans('admin::dashboard.total_orders'),
            'value' => number_format($totalOrders),
            'hint' => trans('admin::dashboard.hints.total_orders'),
        ])
    @endHasAccess

    @if ($showTreatmentStats ?? false)
        @hasAccess('admin.orders.index')
            @include('admin::partials.fc_saas_stat', [
                'variant' => 'violet',
                'icon' => 'fa-heart',
                'label' => trans('admin::dashboard.treatment_sales'),
                'value' => $treatmentSales->KMBTFormat(),
                'valueTitle' => $treatmentSales->format(),
                'hint' => trans('admin::dashboard.hints.treatment_sales'),
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'mint',
                'icon' => 'fa-calendar-check-o',
                'label' => trans('admin::dashboard.today_appointments'),
                'value' => number_format($todayAppointments),
                'hint' => trans('admin::dashboard.hints.today_appointments'),
            ])
        @endHasAccess
    @endif

    @hasAccess('admin.products.index')
        @include('admin::partials.fc_saas_stat', [
            'variant' => 'indigo',
            'icon' => 'fa-cube',
            'label' => trans('admin::dashboard.total_products'),
            'value' => number_format($totalProducts),
            'hint' => trans('admin::dashboard.hints.total_products'),
        ])
    @endHasAccess

    @hasAccess('admin.users.index')
        @include('admin::partials.fc_saas_stat', [
            'variant' => 'peach',
            'icon' => 'fa-users',
            'label' => trans('admin::dashboard.total_customers'),
            'value' => number_format($totalCustomers),
            'hint' => trans('admin::dashboard.hints.total_customers'),
        ])
    @endHasAccess

    @if ($showLoyaltyMembersCard ?? false)
        @hasAccess('admin.loyalty.members.index')
            @php
                $loyaltyActiveCount = number_format($loyaltyMembersWithBalance);
            @endphp
            @include('admin::partials.fc_saas_stat', [
                'variant' => 'gold',
                'icon' => 'fa-star',
                'label' => \Modules\Admin\Support\AdminLang::get('dashboard.loyalty_members'),
                'value' => number_format($loyaltyMembersTotal),
                'hint' => \Modules\Admin\Support\AdminLang::get('dashboard.hints.loyalty_members', [
                    'active' => $loyaltyActiveCount,
                ]),
                'url' => $loyaltyMembersUrl,
                'cta' => \Modules\Admin\Support\AdminLang::get('dashboard.loyalty_members_cta'),
            ])
        @endHasAccess
    @endif
</div>
