@hasAccess('admin.orders.index')
    <div class="dashboard-welcome-banner">
        <div class="dashboard-welcome-banner__content">
            <span class="dashboard-welcome-banner__date">
                {{ now()->translatedFormat('l, j F Y') }}
            </span>
            <h2 class="dashboard-welcome-banner__title">
                {{ trans('admin::dashboard.welcome_back') }}, {{ auth()->user()->first_name ?? 'Admin' }}! 👋
            </h2>
            <p class="dashboard-welcome-banner__subtitle">{{ trans('admin::dashboard.hero_subtitle') }}</p>
            <div class="dashboard-welcome-banner__actions">
                @hasAccess('admin.orders.index')
                    <a href="{{ route('admin.orders.index') }}" class="dashboard-welcome-banner__btn dashboard-welcome-banner__btn--primary">
                        <i class="fa fa-shopping-cart"></i> {{ trans('admin::dashboard.review_orders') }}
                    </a>
                @endHasAccess
                @if (is_module_enabled('TreatmentReservation'))
                    @hasAccess('admin.treatment_reservations.index')
                        <a href="{{ route('admin.treatment_reservations.portal.job_sheet') }}" class="dashboard-welcome-banner__btn dashboard-welcome-banner__btn--outline">
                            <i class="fa fa-clipboard"></i> {{ trans('admin::dashboard.view_job_pipeline') }}
                        </a>
                    @endHasAccess
                @endif
            </div>
        </div>
        <div class="dashboard-welcome-banner__deco" aria-hidden="true">
            @if (file_exists(public_path('images/dashboard/trophy.png')))
                <img src="{{ asset('images/dashboard/trophy.png') }}" alt="" class="dashboard-welcome-banner__trophy">
            @else
                <span class="dashboard-welcome-banner__trophy-emoji">🏆</span>
            @endif
        </div>
    </div>

    <div class="dashboard-hero">
        <div class="dashboard-hero__stats">
            @include('admin::partials.fc_saas_stat', [
                'variant' => 'hero',
                'icon' => 'fa-shopping-cart',
                'label' => trans('admin::dashboard.total_sales'),
                'value' => str_replace(currency(), currency_symbol_fallback(currency()), $totalSales->format()),
                'valueTitle' => str_replace(currency(), currency_symbol_fallback(currency()), $totalSales->format()),
                'hint' => null,
                'trend' => $trends['sales'] ?? null,
                'sparkline' => null,
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'hero',
                'icon' => 'fa-calendar',
                'label' => trans('admin::dashboard.this_month_sales'),
                'value' => str_replace(currency(), currency_symbol_fallback(currency()), $thisMonthSales->format()),
                'valueTitle' => str_replace(currency(), currency_symbol_fallback(currency()), $thisMonthSales->format()),
                'hint' => null,
                'trend' => $trends['orders'] ?? null,
                'sparkline' => null,
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'hero',
                'icon' => 'fa-shopping-bag',
                'label' => trans('admin::dashboard.today_orders'),
                'value' => number_format($todayOrdersCount).' '.trans('admin::dashboard.orders_short'),
                'hint' => null,
                'trend' => null,
                'sparkline' => null,
                'url' => route('admin.orders.index', ['date' => 'today']),
                'cta' => trans('admin::dashboard.view_all'),
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'hero',
                'icon' => 'fa-calendar-check-o',
                'label' => trans('admin::dashboard.today_appointments'),
                'value' => number_format($todayAppointmentsCount).' '.trans('admin::dashboard.appointments_short'),
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
