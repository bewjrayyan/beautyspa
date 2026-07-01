@php
    $quickLinks = array_values(array_filter([
        [
            'permission' => 'admin.orders.index',
            'url' => route('admin.orders.index'),
            'icon' => 'fa-shopping-bag',
            'label' => trans('admin::dashboard.quick_links.orders'),
        ],
        is_module_enabled('TreatmentReservation') ? [
            'permission' => 'admin.treatment_reservations.index',
            'url' => route('admin.treatment_reservations.calendar'),
            'icon' => 'fa-calendar',
            'label' => trans('admin::dashboard.quick_links.reservations'),
        ] : null,
        [
            'permission' => 'admin.products.index',
            'url' => route('admin.products.index'),
            'icon' => 'fa-cube',
            'label' => trans('admin::dashboard.quick_links.products'),
        ],
        [
            'permission' => 'admin.reports.index',
            'url' => route('admin.reports.index'),
            'icon' => 'fa-bar-chart',
            'label' => trans('admin::dashboard.quick_links.reports'),
        ],
        ($showLoyaltyMembersCard ?? false) ? [
            'permission' => 'admin.loyalty.members.index',
            'url' => $loyaltyMembersUrl,
            'icon' => 'fa-star',
            'label' => trans('admin::dashboard.quick_links.loyalty'),
        ] : null,
        is_module_enabled('Coupon') ? [
            'permission' => 'admin.coupons.index',
            'url' => route('admin.coupons.index'),
            'icon' => 'fa-ticket',
            'label' => trans('admin::dashboard.quick_links.coupons'),
        ] : null,
        is_module_enabled('SpaBranch') ? [
            'permission' => 'admin.spa_branches.index',
            'url' => route('admin.spa_branches.index'),
            'icon' => 'fa-map-marker',
            'label' => trans('admin::dashboard.quick_links.spa_branches'),
        ] : null,
        is_module_enabled('Beautician') ? [
            'permission' => 'admin.beauticians.index',
            'url' => route('admin.beauticians.index'),
            'icon' => 'fa-user-md',
            'label' => trans('admin::dashboard.quick_links.beauticians'),
        ] : null,
    ]));
@endphp

@if (count($quickLinks) > 0)
    <nav class="dashboard-quick-links" aria-label="{{ trans('admin::dashboard.quick_links.title') }}">
        <span class="dashboard-quick-links__title">{{ trans('admin::dashboard.quick_links.title') }}</span>
        <div class="dashboard-quick-links__list">
            @foreach ($quickLinks as $link)
                @hasAccess($link['permission'])
                    <a href="{{ $link['url'] }}" class="dashboard-quick-link">
                        <i class="fa {{ $link['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endHasAccess
            @endforeach
        </div>
    </nav>
@endif
