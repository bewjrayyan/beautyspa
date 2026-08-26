@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('transaction::transactions.transactions'))

    <li class="active">{{ trans('transaction::transactions.transactions') }}</li>
@endcomponent

@section('content')
    <div class="transactions-index">
        <p class="transactions-index__lead">{{ trans('transaction::transactions.lead') }}</p>

        <div class="row transactions-index__stats">
            <div class="col-sm-3 col-xs-6">
                <div class="transactions-index__stat">
                    <span class="transactions-index__stat-icon transactions-index__stat-icon--total">
                        <i class="fa fa-list" aria-hidden="true"></i>
                    </span>
                    <div class="transactions-index__stat-body">
                        <span class="transactions-index__stat-label">{{ trans('transaction::transactions.stats.total') }}</span>
                        <strong class="transactions-index__stat-value">{{ number_format($stats['total']) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="transactions-index__stat">
                    <span class="transactions-index__stat-icon transactions-index__stat-icon--offline">
                        <i class="fa fa-university" aria-hidden="true"></i>
                    </span>
                    <div class="transactions-index__stat-body">
                        <span class="transactions-index__stat-label">{{ trans('transaction::transactions.stats.offline') }}</span>
                        <strong class="transactions-index__stat-value">{{ number_format($stats['offline']) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="transactions-index__stat">
                    <span class="transactions-index__stat-icon transactions-index__stat-icon--online">
                        <i class="fa fa-credit-card" aria-hidden="true"></i>
                    </span>
                    <div class="transactions-index__stat-body">
                        <span class="transactions-index__stat-label">{{ trans('transaction::transactions.stats.online') }}</span>
                        <strong class="transactions-index__stat-value">{{ number_format($stats['online']) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="transactions-index__stat">
                    <span class="transactions-index__stat-icon transactions-index__stat-icon--today">
                        <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                    </span>
                    <div class="transactions-index__stat-body">
                        <span class="transactions-index__stat-label">{{ trans('transaction::transactions.stats.today') }}</span>
                        <strong class="transactions-index__stat-value">{{ number_format($stats['today']) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-primary transactions-index__table">
            <div class="box-header with-border transactions-index__table-head">
                <h3 class="box-title">
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                    {{ trans('transaction::transactions.list_title') }}
                </h3>
            </div>

            <div class="box-body">
                <ul class="nav nav-tabs transactions-index__tabs" role="tablist">
                    <li class="active" role="presentation">
                        <a href="#transactions-offline" data-toggle="tab" role="tab" aria-controls="transactions-offline">
                            <i class="fa fa-university" aria-hidden="true"></i>
                            {{ trans('transaction::transactions.tabs.offline') }}
                            <span class="transactions-index__tab-count">{{ number_format($stats['offline']) }}</span>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#transactions-online" data-toggle="tab" role="tab" aria-controls="transactions-online">
                            <i class="fa fa-credit-card" aria-hidden="true"></i>
                            {{ trans('transaction::transactions.tabs.online') }}
                            <span class="transactions-index__tab-count">{{ number_format($stats['online']) }}</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content transactions-index__tab-content">
                    <div class="tab-pane fade in active" id="transactions-offline" role="tabpanel">
                        <div class="index-table" id="transactions-offline-table">
                            @component('admin::components.table')
                                @slot('thead')
                                    <tr>
                                        <th>{{ trans('transaction::transactions.table.order_id') }}</th>
                                        <th>{{ trans('transaction::transactions.table.customer') }}</th>
                                        <th>{{ trans('transaction::transactions.table.beautician') }}</th>
                                        <th>{{ trans('transaction::transactions.table.branch') }}</th>
                                        <th>{{ trans('transaction::transactions.table.transaction_id') }}</th>
                                        <th>{{ trans('transaction::transactions.table.payment_method') }}</th>
                                        <th>{{ trans('transaction::transactions.table.payment_status') }}</th>
                                        <th>{{ trans('transaction::transactions.table.order_total') }}</th>
                                        <th data-sort>{{ trans('admin::admin.table.created') }}</th>
                                        <th class="text-center">{{ trans('transaction::transactions.table.actions') }}</th>
                                    </tr>
                                @endslot
                            @endcomponent
                        </div>
                    </div>

                    <div class="tab-pane fade" id="transactions-online" role="tabpanel">
                        <div class="index-table" id="transactions-online-table">
                            @component('admin::components.table')
                                @slot('thead')
                                    <tr>
                                        <th>{{ trans('transaction::transactions.table.order_id') }}</th>
                                        <th>{{ trans('transaction::transactions.table.customer') }}</th>
                                        <th>{{ trans('transaction::transactions.table.beautician') }}</th>
                                        <th>{{ trans('transaction::transactions.table.branch') }}</th>
                                        <th>{{ trans('transaction::transactions.table.transaction_id') }}</th>
                                        <th>{{ trans('transaction::transactions.table.payment_method') }}</th>
                                        <th>{{ trans('transaction::transactions.table.payment_status') }}</th>
                                        <th>{{ trans('transaction::transactions.table.order_total') }}</th>
                                        <th data-sort>{{ trans('admin::admin.table.created') }}</th>
                                        <th class="text-center">{{ trans('transaction::transactions.table.actions') }}</th>
                                    </tr>
                                @endslot
                            @endcomponent
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @vite(['modules/Transaction/Resources/assets/admin/sass/main.scss'])
@endpush

@push('scripts')
    <script type="module">
        const tableColumns = [
            { data: 'order_id', width: '7%' },
            { data: 'customer', orderable: false, searchable: false },
            { data: 'beautician_name', orderable: false, searchable: false },
            { data: 'spa_branch', orderable: false, searchable: false },
            { data: 'transaction_id', orderable: false, searchable: false },
            { data: 'payment_method', orderable: false, searchable: false },
            { data: 'payment_status', orderable: false, searchable: false },
            { data: 'order_total', orderable: false, searchable: false },
            { data: 'created', name: 'created_at', width: '12%' },
            {
                data: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center',
                width: '9%',
            },
        ];

        function initChannelTable(selector, channel) {
            DataTable.set(selector, {
                routePrefix: 'transactions',
                routes: {
                    table: 'table',
                },
            });

            return new DataTable(selector, {
                order: [[8, 'desc']],
                columns: tableColumns,
                ajax: {
                    url: window.AestheticCart.baseUrl + '/admin/transactions/index/table',
                    data: function (data) {
                        data.table = true;
                        data.channel = channel;
                    },
                },
            });
        }

        const offlineTable = initChannelTable('#transactions-offline-table .table', 'offline');
        let onlineTable = null;

        $('a[data-toggle="tab"][href="#transactions-online"]').one('shown.bs.tab', function () {
            onlineTable = initChannelTable('#transactions-online-table .table', 'online');
        });

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
            const target = $(event.target).attr('href');
            const api = target === '#transactions-online'
                ? (onlineTable?.dataTable || onlineTable)
                : (offlineTable?.dataTable || offlineTable);

            if (api && typeof api.columns === 'function') {
                api.columns.adjust();
            } else if ($.fn.dataTable.isDataTable(target === '#transactions-online'
                ? '#transactions-online-table .table'
                : '#transactions-offline-table .table')) {
                $(target === '#transactions-online'
                    ? '#transactions-online-table .table'
                    : '#transactions-offline-table .table')
                    .DataTable()
                    .columns.adjust();
            }
        });

        document.addEventListener('click', (event) => {
            const button = event.target.closest('.js-copy-tx-id');

            if (!button) {
                return;
            }

            event.preventDefault();

            const text = button.getAttribute('data-copy') || '';

            if (!text) {
                return;
            }

            navigator.clipboard.writeText(text).then(() => {
                if (typeof window.success === 'function') {
                    window.success(@json(trans('transaction::transactions.copied')));
                }

                button.classList.add('is-copied');

                window.setTimeout(() => button.classList.remove('is-copied'), 1500);
            }).catch(() => {
                if (typeof window.error === 'function') {
                    window.error(@json(trans('transaction::transactions.copy_failed')));
                }
            });
        });
    </script>
@endpush
