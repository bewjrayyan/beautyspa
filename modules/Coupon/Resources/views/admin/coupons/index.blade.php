@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('coupon::coupons.coupons'))

    <li class="active">{{ trans('coupon::coupons.coupons') }}</li>
@endcomponent

@section('content')
    <div class="coupon-admin">
        <header class="coupon-admin-hero">
            <div class="coupon-admin-hero__main">
                <h1 class="coupon-admin-hero__title">
                    <i class="fa fa-ticket" aria-hidden="true"></i>
                    {{ trans('coupon::coupons.coupons') }}
                </h1>
                <p class="coupon-admin-hero__lead">{{ trans('coupon::coupons.index.lead') }}</p>
            </div>
            <div class="coupon-admin-hero__actions">
                @hasAccess('admin.coupons.create')
                    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary coupon-admin-hero__btn">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        {{ trans('admin::resource.create', ['resource' => trans('coupon::coupons.coupon')]) }}
                    </a>
                @endHasAccess
            </div>
        </header>

        <div class="coupon-admin-stats">
            <div class="coupon-admin-stats__stat">
                <span class="coupon-admin-stats__icon coupon-admin-stats__icon--primary">
                    <i class="fa fa-tags" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="coupon-admin-stats__label">{{ trans('coupon::coupons.index.stats_total') }}</span>
                    <strong class="coupon-admin-stats__value">{{ number_format($stats['total']) }}</strong>
                </div>
            </div>
            <div class="coupon-admin-stats__stat">
                <span class="coupon-admin-stats__icon coupon-admin-stats__icon--success">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="coupon-admin-stats__label">{{ trans('coupon::coupons.index.stats_valid') }}</span>
                    <strong class="coupon-admin-stats__value">{{ number_format($stats['valid_now']) }}</strong>
                    <span class="coupon-admin-stats__hint">{{ trans('coupon::coupons.index.stats_valid_hint') }}</span>
                </div>
            </div>
            <div class="coupon-admin-stats__stat">
                <span class="coupon-admin-stats__icon coupon-admin-stats__icon--purple">
                    <i class="fa fa-power-off" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="coupon-admin-stats__label">{{ trans('coupon::coupons.index.stats_active') }}</span>
                    <strong class="coupon-admin-stats__value">{{ number_format($stats['active']) }}</strong>
                </div>
            </div>
            <div class="coupon-admin-stats__stat">
                <span class="coupon-admin-stats__icon coupon-admin-stats__icon--shipping">
                    <i class="fa fa-truck" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="coupon-admin-stats__label">{{ trans('coupon::coupons.index.stats_free_shipping') }}</span>
                    <strong class="coupon-admin-stats__value">{{ number_format($stats['free_shipping']) }}</strong>
                </div>
            </div>
        </div>

        @if ($featured->isNotEmpty())
            <section class="coupon-admin-spotlight" aria-label="{{ trans('coupon::coupons.index.spotlight_title') }}">
                <div class="coupon-admin-card__head">
                    <div>
                        <h2>{{ trans('coupon::coupons.index.spotlight_title') }}</h2>
                        <p>{{ trans('coupon::coupons.index.spotlight_lead') }}</p>
                    </div>
                </div>
                <div class="coupon-admin-spotlight__grid">
                    @foreach ($featured as $coupon)
                        @php
                            if ($coupon->is_percent) {
                                $raw = (float) ($coupon->getAttributes()['value'] ?? 0);
                                $discount = fmod($raw, 1.0) === 0.0
                                    ? (int) $raw . '%'
                                    : rtrim(rtrim(number_format($raw, 2, '.', ''), '0'), '.') . '%';
                            } else {
                                $discount = $coupon->value->format();
                            }
                        @endphp
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="coupon-admin-spotlight__card">
                            <div class="coupon-admin-spotlight__ticket">
                                <div class="coupon-admin-spotlight__stub">
                                    <span class="coupon-admin-spotlight__stub-label">{{ trans('coupon::coupons.index.ticket_off') }}</span>
                                    <span class="coupon-admin-spotlight__discount">{{ $discount }}</span>
                                </div>
                                <div class="coupon-admin-spotlight__tear" aria-hidden="true"></div>
                                <div class="coupon-admin-spotlight__main">
                                    <span class="coupon-admin-spotlight__code">{{ $coupon->code }}</span>
                                    <h3 class="coupon-admin-spotlight__name">{{ $coupon->name }}</h3>
                                    @if ($coupon->free_shipping)
                                        <span class="coupon-admin-spotlight__tag">
                                            <i class="fa fa-truck" aria-hidden="true"></i>
                                            {{ trans('coupon::coupons.index.free_shipping') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="coupon-admin-card coupon-admin-card--table">
            <div class="coupon-admin-card__head">
                <div>
                    <h2><i class="fa fa-list" aria-hidden="true"></i> {{ trans('coupon::coupons.index.table_title') }}</h2>
                    <p>{{ trans('coupon::coupons.index.table_lead') }}</p>
                </div>
            </div>
            <div class="coupon-admin-card__body index-table" id="coupons-table">
                @component('admin::components.table')
                    @slot('thead')
                        <tr>
                            @include('admin::partials.table.select_all')

                            <th data-sort>{{ trans('admin::admin.table.id') }}</th>
                            <th>{{ trans('coupon::coupons.table.name') }}</th>
                            <th>{{ trans('coupon::coupons.table.code') }}</th>
                            <th>{{ trans('coupon::coupons.table.discount') }}</th>
                            <th>{{ trans('coupon::coupons.index.validity') }}</th>
                            <th class="text-right">{{ trans('coupon::coupons.index.usage') }}</th>
                            <th>{{ trans('admin::admin.table.status') }}</th>
                            <th data-sort>{{ trans('admin::admin.table.created') }}</th>
                        </tr>
                    @endslot
                @endcomponent
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module">
        DataTable.set('#coupons-table .table', {
            routePrefix: 'coupons',
            routes: {
                table: 'table',
                edit: 'edit',
                destroy: 'destroy',
            },
        });

        new DataTable('#coupons-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'name', name: 'translations.name', orderable: false, defaultContent: '' },
                { data: 'code' },
                { data: 'discount', name: 'value', orderable: false, searchable: false },
                { data: 'validity', orderable: false, searchable: false },
                { data: 'usage', orderable: false, searchable: false, className: 'text-right' },
                { data: 'status', name: 'is_active', searchable: false },
                { data: 'created', name: 'created_at' },
            ],
        });
    </script>
@endpush

@push('styles')
    @vite(['modules/Coupon/Resources/assets/admin/sass/main.scss'])
@endpush
