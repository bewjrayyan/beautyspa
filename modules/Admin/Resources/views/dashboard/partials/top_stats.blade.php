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
                'hint' => trans('admin::dashboard.hints.total_sales'),
                'trend' => $trends['sales'] ?? null,
                'sparkline' => null,
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'hero',
                'icon' => 'fa-balance-scale',
                'label' => trans('admin::dashboard.net_sales'),
                'value' => str_replace(currency(), currency_symbol_fallback(currency()), $netSales->format()),
                'valueTitle' => str_replace(currency(), currency_symbol_fallback(currency()), $netSales->format()),
                'hint' => trans('admin::dashboard.hints.net_sales'),
                'trend' => null,
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

    @if (! empty($salesByBranch))
        <section class="dashboard-branch-sales" aria-labelledby="dashboard-branch-sales-title">
            <div class="dashboard-branch-sales__head">
                <div>
                    <h3 id="dashboard-branch-sales-title" class="dashboard-branch-sales__title">
                        {{ trans('admin::dashboard.sales_by_branch') }}
                    </h3>
                    <p class="dashboard-branch-sales__help">{{ trans('admin::dashboard.sales_by_branch_help') }}</p>
                </div>
                <div class="dashboard-branch-sales__master">
                    <span class="dashboard-branch-sales__master-label">{{ trans('admin::dashboard.total_sales') }}</span>
                    <strong class="dashboard-branch-sales__master-value">
                        {{ str_replace(currency(), currency_symbol_fallback(currency()), $totalSales->format()) }}
                    </strong>
                </div>
            </div>
            <div class="dashboard-branch-sales__grid">
                @foreach ($salesByBranch as $branch)
                    <article class="dashboard-branch-sales__card">
                        <h4 class="dashboard-branch-sales__branch">{{ $branch['name'] }}</h4>
                        <p class="dashboard-branch-sales__amount" title="{{ $branch['total']->format() }}">
                            {{ str_replace(currency(), currency_symbol_fallback(currency()), $branch['total']->format()) }}
                        </p>
                        <dl class="dashboard-branch-sales__meta">
                            <div>
                                <dt>{{ trans('admin::dashboard.branch_refunded') }}</dt>
                                <dd>{{ str_replace(currency(), currency_symbol_fallback(currency()), $branch['refunded']->format()) }}</dd>
                            </div>
                            <div>
                                <dt>{{ trans('admin::dashboard.branch_net') }}</dt>
                                <dd>{{ str_replace(currency(), currency_symbol_fallback(currency()), $branch['net']->format()) }}</dd>
                            </div>
                        </dl>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endHasAccess
