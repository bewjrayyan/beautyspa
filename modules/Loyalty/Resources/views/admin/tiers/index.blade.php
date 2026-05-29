@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('loyalty::tiers.tiers'))

    <li class="active">{{ trans('loyalty::tiers.tiers') }}</li>
@endcomponent

@section('content')
    @php
        $currencySymbol = currency_symbol(setting('default_currency'));
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
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--success">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::tiers.index.stats_active') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($stats['active']) }}</strong>
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--purple">
                    <i class="fa fa-users" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::tiers.index.stats_members') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($stats['total_members']) }}</strong>
                </div>
            </div>
        </div>

        @if ($tiers->isNotEmpty())
            <section class="loyalty-tier-ladder" aria-label="{{ trans('loyalty::tiers.index.pipeline_title') }}">
                <div class="loyalty-page-card__head loyalty-tier-ladder__head">
                    <div>
                        <h2>{{ trans('loyalty::tiers.index.pipeline_title') }}</h2>
                        <p>{{ trans('loyalty::tiers.index.pipeline_lead') }}</p>
                    </div>
                </div>
                <div class="loyalty-tier-ladder__track">
                    @foreach ($tiers as $index => $tier)
                        <a
                            href="{{ route('admin.loyalty.tiers.edit', $tier) }}"
                            class="loyalty-tier-card loyalty-tier-card--{{ ($index % 4) + 1 }} {{ $tier->is_active ? '' : 'loyalty-tier-card--inactive' }}"
                        >
                            <span class="loyalty-tier-card__badge">
                                <i class="fa fa-star" aria-hidden="true"></i>
                                {{ $tier->earn_multiplier }}×
                            </span>
                            <h3 class="loyalty-tier-card__name">{{ $tier->name }}</h3>
                            <p class="loyalty-tier-card__slug">{{ $tier->slug }}</p>
                            <ul class="loyalty-tier-card__meta">
                                <li>
                                    <span>{{ trans('loyalty::tiers.table.min_spend') }}</span>
                                    <strong>{{ $currencySymbol }} {{ number_format($tier->min_lifetime_spend, 2) }}</strong>
                                </li>
                                <li>
                                    <span>{{ trans('loyalty::reports.members') }}</span>
                                    <strong>{{ trans('loyalty::tiers.index.members_count', ['count' => number_format($tier->wallets_count)]) }}</strong>
                                </li>
                            </ul>
                            @unless ($tier->is_active)
                                <span class="loyalty-tier-card__status">{{ trans('loyalty::tiers.index.inactive') }}</span>
                            @endunless
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="loyalty-page-card loyalty-page-card--table">
            <div class="loyalty-page-card__head">
                <div>
                    <h2><i class="fa fa-list" aria-hidden="true"></i> {{ trans('loyalty::tiers.index.table_title') }}</h2>
                </div>
            </div>
            <div class="loyalty-page-card__body index-table" id="loyalty-tiers-table">
                @component('admin::components.table')
                    @slot('thead')
                        <tr>
                            @include('admin::partials.table.select_all')

                            <th data-sort>{{ trans('admin::admin.table.id') }}</th>
                            <th>{{ trans('loyalty::tiers.table.name') }}</th>
                            <th>{{ trans('loyalty::tiers.table.min_spend') }}</th>
                            <th>{{ trans('loyalty::tiers.table.multiplier') }}</th>
                            <th>{{ trans('admin::admin.table.status') }}</th>
                        </tr>
                    @endslot
                @endcomponent
            </div>
        </div>
    </div>
@endsection

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

        new DataTable('#loyalty-tiers-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'name' },
                { data: 'min_spend', name: 'min_lifetime_spend', searchable: false },
                { data: 'multiplier', name: 'earn_multiplier', searchable: false },
                { data: 'status', name: 'is_active', searchable: false },
            ],
        });
    </script>
@endpush

@push('styles')
    @vite(['modules/Loyalty/Resources/assets/admin/sass/main.scss'])
@endpush
