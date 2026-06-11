@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('loyalty::members.members'))

    <li class="active">{{ trans('loyalty::members.members') }}</li>
@endcomponent

@section('content')
    @php
        use Modules\Loyalty\Support\LoyaltyLang;

        $currencySymbol = currency_symbol(setting('default_currency'));
        $totalMembers = max(1, (int) $stats['total']);
        $membersSearchPlaceholder = LoyaltyLang::get('members.index.search_placeholder');
    @endphp

    <div class="loyalty-admin loyalty-members">
        <header class="loyalty-page-hero loyalty-page-hero--members">
            <div class="loyalty-page-hero__main">
                <h1 class="loyalty-page-hero__title">
                    <i class="fa fa-users" aria-hidden="true"></i>
                    {{ trans('loyalty::members.members') }}
                </h1>
                <p class="loyalty-page-hero__lead">{{ trans('loyalty::members.index.lead') }}</p>
            </div>
            <div class="loyalty-page-hero__actions">
                <a href="{{ route('admin.loyalty.reports.index') }}" class="btn btn-default loyalty-page-hero__btn">
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    {{ trans('loyalty::sidebar.reports') }}
                </a>
                <a href="{{ route('admin.loyalty.tiers.index') }}" class="btn btn-default loyalty-page-hero__btn">
                    <i class="fa fa-star" aria-hidden="true"></i>
                    {{ trans('loyalty::tiers.tiers') }}
                </a>
            </div>
        </header>

        <div class="loyalty-members__stamp-tools">
            @include('loyalty::admin.members.partials.stamp-lookup', [
                'stampLookup' => $stampLookup ?? null,
            ])

            @if (($pendingStampRedemptions ?? collect())->isNotEmpty())
                <div class="loyalty-member-card loyalty-stamp-pending">
                    <div class="loyalty-member-card__head">
                        <h3>
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            {{ trans('loyalty::members.stamps.pending_queue_title') }}
                        </h3>
                        <p>{{ trans('loyalty::members.stamps.pending_queue_lead') }}</p>
                    </div>
                    <div class="loyalty-member-card__body loyalty-stamp-pending__body">
                        @foreach ($pendingStampRedemptions as $pending)
                            <div class="loyalty-stamp-pending__item">
                                <div>
                                    <strong>{{ $pending->user?->full_name }}</strong>
                                    <span class="text-muted">· {{ $pending->program?->name }}</span>
                                    <br>
                                    <code>{{ $pending->redemption_code }}</code>
                                </div>
                                <a
                                    href="{{ route('admin.loyalty.members.index', ['code' => $pending->redemption_code]) }}"
                                    class="btn btn-default btn-xs"
                                >
                                    {{ trans('loyalty::members.stamps.verify') }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="loyalty-page-stats loyalty-page-stats--4">
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--primary">
                    <i class="fa fa-users" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::members.index.stats_total') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($stats['total']) }}</strong>
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--success">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::members.index.stats_active') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($stats['active']) }}</strong>
                    <span class="loyalty-page-stats__hint">
                        {{ trans('loyalty::members.index.stats_active_hint') }}
                    </span>
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--earned">
                    <i class="fa fa-database" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::members.index.stats_points') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($stats['outstanding_points']) }}</strong>
                    <span class="loyalty-page-stats__hint">
                        ≈ {{ $currencySymbol }} {{ number_format($stats['liability_rm'], 2) }}
                    </span>
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--purple">
                    <i class="fa fa-money" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::members.index.stats_spend') }}</span>
                    <strong class="loyalty-page-stats__value loyalty-page-stats__value--compact">
                        {{ $currencySymbol }} {{ number_format($stats['total_spend'], 0) }}
                    </strong>
                </div>
            </div>
        </div>

        @if ($tier_breakdown->isNotEmpty())
            <div class="loyalty-members-tier-chips">
                <span class="loyalty-members-tier-chips__label">{{ trans('loyalty::members.index.by_tier') }}</span>
                @foreach ($tier_breakdown as $row)
                    @php
                        $pct = round(($row->members / $totalMembers) * 100, 1);
                    @endphp
                    <span class="loyalty-members-tier-chips__chip" title="{{ $pct }}%">
                        <i class="fa fa-star" aria-hidden="true"></i>
                        {{ $row->name }}
                        <strong>{{ number_format($row->members) }}</strong>
                    </span>
                @endforeach
            </div>
        @endif

        <div class="loyalty-page-card loyalty-page-card--table loyalty-members-table-card">
            <div class="loyalty-page-card__head">
                <div class="loyalty-page-card__head-text">
                    <h2><i class="fa fa-list" aria-hidden="true"></i> {{ trans('loyalty::members.index.table_title') }}</h2>
                    <p>{{ trans('loyalty::members.index.table_lead') }}</p>
                </div>
                <div
                    class="loyalty-members-table-card__search"
                    id="loyalty-members-table-search"
                ></div>
            </div>
            <div class="loyalty-page-card__body index-table" id="loyalty-members-table">
                @component('admin::components.table')
                    @slot('thead')
                        <tr>
                            <th data-sort>{{ trans('admin::admin.table.id') }}</th>
                            <th>{{ trans('loyalty::members.table.customer') }}</th>
                            <th>{{ trans('loyalty::members.table.tier') }}</th>
                            <th class="text-right loyalty-members-table__col-balance">{{ trans('loyalty::members.table.balance') }}</th>
                            <th class="text-right loyalty-members-table__col-spend">{{ trans('loyalty::members.table.lifetime_spend') }}</th>
                            <th class="text-right loyalty-members-table__col-actions">{{ trans('loyalty::members.index.actions') }}</th>
                        </tr>
                    @endslot
                @endcomponent
            </div>
        </div>
    </div>
@endsection

@push('globals')
    <script>
        AestheticCart.langs['loyalty::members.index.search_placeholder'] = @json($membersSearchPlaceholder);
    </script>
@endpush

@push('scripts')
    <script type="module">
        DataTable.set('#loyalty-members-table .table', {
            routePrefix: 'loyalty/members',
            routes: {
                table: 'table',
                show: 'show',
            },
        });

        new DataTable(
            '#loyalty-members-table .table',
            {
                layout: {
                    topEnd: {
                        search: {
                            placeholder: trans('loyalty::members.index.search_placeholder'),
                        },
                    },
                },
                columns: [
                    { data: 'id', width: '5%', searchable: false },
                    { data: 'customer', name: 'customer', orderable: false, searchable: true },
                    { data: 'tier', orderable: false, searchable: false },
                    {
                        data: 'balance',
                        searchable: false,
                        className: 'text-right loyalty-members-table__col-balance',
                    },
                    {
                        data: 'lifetime_spend',
                        searchable: false,
                        className: 'text-right loyalty-members-table__col-spend',
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-right loyalty-members-table__col-actions',
                    },
                ],
            },
            function () {
                const $container = this.element.closest('.dt-container');
                const $search = $container.find('.dt-search');
                const $mount = $('#loyalty-members-table-search');
                const $searchInput = $search.find('input');

                if ($mount.length && $search.length) {
                    $search.appendTo($mount);
                }

                $container.find('.dt-layout-row').first().find('.dt-layout-end').empty();

                const query = new URLSearchParams(window.location.search).get('search');

                if (query && $searchInput.length) {
                    $searchInput.val(query).trigger('input');
                }
            }
        );
    </script>
@endpush

@push('styles')
    @vite(['modules/Loyalty/Resources/assets/admin/sass/main.scss'])
@endpush
