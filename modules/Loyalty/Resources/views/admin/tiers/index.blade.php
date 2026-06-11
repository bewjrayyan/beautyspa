@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('loyalty::tiers.tiers'))

    <li class="active">{{ trans('loyalty::tiers.tiers') }}</li>
@endcomponent

@section('content')
    @php
        use Modules\Loyalty\Support\LoyaltyLang;

        $currencySymbol = currency_symbol(setting('default_currency'));
        $totalMembers = max(1, (int) $stats['total_members']);
        $tiersSearchPlaceholder = LoyaltyLang::get('tiers.index.search_placeholder');
    @endphp

    <div class="loyalty-admin loyalty-tiers">
        <header class="loyalty-page-hero loyalty-page-hero--tiers">
            <div class="loyalty-page-hero__main">
                <h1 class="loyalty-page-hero__title">
                    <i class="fa fa-star" aria-hidden="true"></i>
                    {{ trans('loyalty::tiers.tiers') }}
                </h1>
                <p class="loyalty-page-hero__lead">{{ trans('loyalty::tiers.index.lead') }}</p>
            </div>
            <div class="loyalty-page-hero__actions">
                @hasAccess('admin.loyalty.tiers.create')
                    <a href="{{ route('admin.loyalty.tiers.create') }}" class="btn btn-primary loyalty-page-hero__btn">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        {{ trans('admin::resource.create', ['resource' => trans('loyalty::tiers.tier')]) }}
                    </a>
                @endHasAccess
                <a href="{{ route('admin.loyalty.members.index') }}" class="btn btn-default loyalty-page-hero__btn">
                    <i class="fa fa-users" aria-hidden="true"></i>
                    {{ trans('loyalty::members.members') }}
                </a>
                <a href="{{ route('admin.loyalty.reports.index') }}" class="btn btn-default loyalty-page-hero__btn">
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    {{ trans('loyalty::sidebar.reports') }}
                </a>
            </div>
        </header>

        <div class="loyalty-page-stats loyalty-page-stats--3">
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--primary">
                    <i class="fa fa-th-large" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::tiers.index.stats_total') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($stats['total']) }}</strong>
                    <span class="loyalty-page-stats__hint">{{ trans('loyalty::tiers.index.stats_total_hint') }}</span>
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--success">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::tiers.index.stats_active') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($stats['active']) }}</strong>
                    <span class="loyalty-page-stats__hint">{{ trans('loyalty::tiers.index.stats_active_hint') }}</span>
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--purple">
                    <i class="fa fa-users" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::tiers.index.stats_members') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($stats['total_members']) }}</strong>
                    <span class="loyalty-page-stats__hint">{{ trans('loyalty::tiers.index.stats_members_hint') }}</span>
                </div>
            </div>
        </div>

        @if ($tiers->isNotEmpty())
            @if ($stats['total_members'] > 0)
                <section class="loyalty-page-card loyalty-tiers-distribution" aria-label="{{ trans('loyalty::tiers.index.distribution_title') }}">
                    <div class="loyalty-page-card__head">
                        <div>
                            <h2><i class="fa fa-pie-chart" aria-hidden="true"></i> {{ trans('loyalty::tiers.index.distribution_title') }}</h2>
                            <p>{{ trans('loyalty::tiers.index.distribution_lead') }}</p>
                        </div>
                    </div>
                    <div class="loyalty-page-card__body loyalty-tiers-distribution__grid">
                        @foreach ($tiers as $tier)
                            @php
                                $pct = round(($tier->wallets_count / $totalMembers) * 100, 1);
                            @endphp
                            <div class="loyalty-tiers-distribution__col loyalty-tiers-distribution__col--{{ $tier->slugThemeClass() }}">
                                <div class="loyalty-tiers-distribution__col-head">
                                    <span class="loyalty-tier-table__pill loyalty-tier-table__pill--{{ $tier->slugThemeClass() }}">
                                        {{ $tier->translatedName() }}
                                    </span>
                                    <strong class="loyalty-tiers-distribution__pct">{{ $pct }}%</strong>
                                </div>
                                <p class="loyalty-tiers-distribution__count">
                                    {{ trans('loyalty::tiers.index.members_count', ['count' => number_format($tier->wallets_count)]) }}
                                </p>
                                <div class="loyalty-tiers-distribution__bar">
                                    <span
                                        class="loyalty-tiers-distribution__fill loyalty-tiers-distribution__fill--{{ $tier->slugThemeClass() }}"
                                        style="width: {{ max($pct, $tier->wallets_count > 0 ? 4 : 0) }}%"
                                    ></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="loyalty-tier-ladder" aria-label="{{ trans('loyalty::tiers.index.pipeline_title') }}">
                <div class="loyalty-page-card__head loyalty-tier-ladder__head">
                    <div>
                        <h2><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> {{ trans('loyalty::tiers.index.pipeline_title') }}</h2>
                        <p>{{ trans('loyalty::tiers.index.pipeline_lead') }}</p>
                    </div>
                </div>
                @php
                    $tierCount = $tiers->count();
                    $tierLadderFullWidth = $tierCount <= 3;
                @endphp
                <div class="loyalty-tier-ladder__track-wrap {{ $tierLadderFullWidth ? 'loyalty-tier-ladder__track-wrap--full' : '' }}">
                    <div
                        class="loyalty-tier-ladder__track {{ $tierLadderFullWidth ? 'loyalty-tier-ladder__track--full' : '' }}"
                        style="--tier-count: {{ $tierCount }}"
                    >
                    @foreach ($tiers as $tier)
                        @if (! $loop->first)
                            <span class="loyalty-tier-ladder__connector" aria-hidden="true">
                                <i class="fa fa-long-arrow-right"></i>
                            </span>
                        @endif

                        @include('loyalty::admin.tiers.partials.ladder-card', [
                            'tier' => $tier,
                            'currencySymbol' => $currencySymbol,
                            'totalMembers' => $totalMembers,
                            'step' => $loop->iteration,
                        ])
                    @endforeach
                    </div>
                </div>
            </section>
        @else
            <div class="loyalty-tiers-empty">
                <span class="loyalty-tiers-empty__icon" aria-hidden="true">
                    <i class="fa fa-star"></i>
                </span>
                <h2>{{ trans('loyalty::tiers.index.empty_title') }}</h2>
                <p>{{ trans('loyalty::tiers.index.empty_lead') }}</p>
                @hasAccess('admin.loyalty.tiers.create')
                    <a href="{{ route('admin.loyalty.tiers.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        {{ trans('admin::resource.create', ['resource' => trans('loyalty::tiers.tier')]) }}
                    </a>
                @endHasAccess
            </div>
        @endif

        <div class="loyalty-page-card loyalty-page-card--table loyalty-tiers-table-card">
            <div class="loyalty-page-card__head">
                <div class="loyalty-page-card__head-text">
                    <h2><i class="fa fa-list" aria-hidden="true"></i> {{ trans('loyalty::tiers.index.table_title') }}</h2>
                    <p>{{ trans('loyalty::tiers.index.table_lead') }}</p>
                </div>
                <div class="loyalty-tiers-table-card__search" id="loyalty-tiers-table-search"></div>
            </div>
            <div class="loyalty-page-card__body index-table" id="loyalty-tiers-table">
                @component('admin::components.table')
                    @slot('thead')
                        <tr>
                            <th data-sort>{{ trans('admin::admin.table.id') }}</th>
                            <th>{{ trans('loyalty::tiers.table.name') }}</th>
                            <th class="text-right">{{ trans('loyalty::tiers.table.min_spend') }}</th>
                            <th class="text-center">{{ trans('loyalty::tiers.table.multiplier') }}</th>
                            <th class="text-right">{{ trans('loyalty::tiers.table.members') }}</th>
                            <th>{{ trans('admin::admin.table.status') }}</th>
                            <th class="text-right loyalty-tiers-table__col-actions">{{ trans('loyalty::tiers.index.actions') }}</th>
                        </tr>
                    @endslot
                @endcomponent
            </div>
        </div>
    </div>
@endsection

@push('globals')
    <script>
        AestheticCart.langs['loyalty::tiers.index.search_placeholder'] = @json($tiersSearchPlaceholder);
    </script>
@endpush

@push('scripts')
    <script type="module">
        DataTable.set('#loyalty-tiers-table .table', {
            routePrefix: 'loyalty/tiers',
            routes: {
                table: 'table',
                edit: 'edit',
                destroy: 'destroy',
            },
        });

        new DataTable(
            '#loyalty-tiers-table .table',
            {
                layout: {
                    topEnd: {
                        search: {
                            placeholder: trans('loyalty::tiers.index.search_placeholder'),
                        },
                    },
                },
                columns: [
                    { data: 'id', width: '5%', searchable: false },
                    { data: 'name' },
                    {
                        data: 'min_spend',
                        name: 'min_lifetime_spend',
                        searchable: false,
                        className: 'text-right',
                    },
                    {
                        data: 'multiplier',
                        name: 'earn_multiplier',
                        searchable: false,
                        className: 'text-center',
                    },
                    {
                        data: 'members',
                        name: 'wallets_count',
                        searchable: false,
                        className: 'text-right',
                    },
                    {
                        data: 'status',
                        name: 'is_active',
                        searchable: false,
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-right loyalty-tiers-table__col-actions',
                    },
                ],
            },
            function () {
                const $container = this.element.closest('.dt-container');
                const $search = $container.find('.dt-search');
                const $mount = $('#loyalty-tiers-table-search');
                const $searchInput = $search.find('input');

                if ($mount.length && $search.length) {
                    $search.appendTo($mount);
                }

                $container.find('.dt-layout-row').first().find('.dt-layout-end').empty();
            }
        );
    </script>
@endpush

@push('styles')
    @vite(['modules/Loyalty/Resources/assets/admin/sass/main.scss'])
@endpush
