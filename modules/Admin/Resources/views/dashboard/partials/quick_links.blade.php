@php
    $quickActionCards = array_values(array_filter([
        [
            'permission' => 'admin.products.create',
            'url' => route('admin.products.create'),
            'icon' => 'fa-plus-circle',
            'label' => trans('admin::dashboard.quick_actions.add_product'),
            'subtitle' => trans('admin::dashboard.quick_actions.add_product_sub'),
            'gradient' => 'qa-card--blue',
        ],
        [
            'permission' => 'admin.users.index',
            'url' => route('admin.users.index'),
            'icon' => 'fa-users',
            'label' => trans('admin::dashboard.quick_actions.approve_users'),
            'subtitle' => trans('admin::dashboard.quick_actions.approve_users_sub'),
            'gradient' => 'qa-card--teal',
        ],
        [
            'permission' => 'admin.reports.index',
            'url' => route('admin.reports.index'),
            'icon' => 'fa-bar-chart',
            'label' => trans('admin::dashboard.quick_actions.view_reports'),
            'subtitle' => trans('admin::dashboard.quick_actions.view_reports_sub'),
            'gradient' => 'qa-card--green',
        ],
        [
            'permission' => 'admin.settings.edit',
            'url' => route('admin.settings.edit'),
            'icon' => 'fa-cog',
            'label' => trans('admin::dashboard.quick_actions.settings'),
            'subtitle' => trans('admin::dashboard.quick_actions.settings_sub'),
            'gradient' => 'qa-card--orange',
        ],
    ]));
@endphp

@if (count($quickActionCards) > 0)
    <div class="dashboard-qa-row">
        {{-- Quick Actions --}}
        <div class="dashboard-panel qa-panel">
            <div class="qa-panel__header">
                <h5 class="qa-panel__title">{{ trans('admin::dashboard.quick_actions.title') }}</h5>
                <p class="qa-panel__subtitle">{{ trans('admin::dashboard.quick_actions.subtitle') }}</p>
            </div>

            <div class="qa-grid">
                @foreach ($quickActionCards as $card)
                    @hasAccess($card['permission'])
                        <a href="{{ $card['url'] }}" class="qa-card {{ $card['gradient'] }}">
                            <span class="qa-card__icon-wrap">
                                <i class="fa {{ $card['icon'] }}" aria-hidden="true"></i>
                            </span>
                            <span class="qa-card__text">
                                <span class="qa-card__label">{{ $card['label'] }}</span>
                                <span class="qa-card__sub">{{ $card['subtitle'] }}</span>
                            </span>
                        </a>
                    @endHasAccess
                @endforeach
            </div>
        </div>

        {{-- Top Beauticians --}}
        @if ($showAppointmentPanels ?? false)
            @include('admin::dashboard.panels.top_beauticians')
        @endif
    </div>
@endif
