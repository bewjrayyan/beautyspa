@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('transaction::transactions.transactions'))

    <li class="active">{{ trans('transaction::transactions.transactions') }}</li>
@endcomponent

@section('content')
    <div class="transactions-index">
        <p class="transactions-index__lead">{{ trans('transaction::transactions.lead') }}</p>

        <div class="row transactions-index__stats">
            <div class="col-sm-4">
                <div class="transactions-index__stat">
                    <span class="transactions-index__stat-icon transactions-index__stat-icon--total">
                        <i class="fa fa-credit-card" aria-hidden="true"></i>
                    </span>
                    <div class="transactions-index__stat-body">
                        <span class="transactions-index__stat-label">{{ trans('transaction::transactions.stats.total') }}</span>
                        <strong class="transactions-index__stat-value">{{ number_format($stats['total']) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
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
            <div class="col-sm-4">
                <div class="transactions-index__stat">
                    <span class="transactions-index__stat-icon transactions-index__stat-icon--week">
                        <i class="fa fa-line-chart" aria-hidden="true"></i>
                    </span>
                    <div class="transactions-index__stat-body">
                        <span class="transactions-index__stat-label">{{ trans('transaction::transactions.stats.week') }}</span>
                        <strong class="transactions-index__stat-value">{{ number_format($stats['week']) }}</strong>
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
            <div class="box-body index-table" id="transactions-table">
                @component('admin::components.table')
                    @slot('thead')
                        <tr>
                            <th>{{ trans('transaction::transactions.table.order_id') }}</th>
                            <th>{{ trans('transaction::transactions.table.customer') }}</th>
                            <th>{{ trans('transaction::transactions.table.transaction_id') }}</th>
                            <th>{{ trans('transaction::transactions.table.payment_method') }}</th>
                            <th>{{ trans('transaction::transactions.table.order_total') }}</th>
                            <th data-sort>{{ trans('admin::admin.table.created') }}</th>
                            <th class="text-center">{{ trans('transaction::transactions.table.actions') }}</th>
                        </tr>
                    @endslot
                @endcomponent
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @vite(['modules/Transaction/Resources/assets/admin/sass/main.scss'])
@endpush

@push('scripts')
    <script type="module">
        DataTable.set('#transactions-table .table', {
            routePrefix: 'transactions',
            routes: {
                table: 'table',
            },
        });

        new DataTable('#transactions-table .table', {
            order: [[5, 'desc']],
            columns: [
                { data: 'order_id', width: '8%' },
                { data: 'customer', orderable: false, searchable: false },
                { data: 'transaction_id' },
                { data: 'payment_method', orderable: false, searchable: false },
                { data: 'order_total', orderable: false, searchable: false },
                { data: 'created', name: 'created_at', width: '14%' },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    width: '10%',
                },
            ],
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
