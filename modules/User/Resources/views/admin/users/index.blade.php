@extends('admin::layout')

@section('title', trans('user::users.users'))

@section('content_header')
    <nav class="admin-users-breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard.index') }}" class="breadcrumb-home-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12 18V15" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10.07 2.81997L3.13999 8.36997C2.35999 8.98997 1.85999 10.3 2.02999 11.28L3.35999 19.24C3.59999 20.66 4.95999 21.81 6.39999 21.81H17.6C19.03 21.81 20.4 20.65 20.64 19.24L21.97 11.28C22.13 10.3 21.63 8.98997 20.86 8.36997L13.93 2.82997C12.86 1.96997 11.13 1.96997 10.07 2.81997Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </li>
            <li class="active">{{ trans('user::users.users') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
    @php
        $inactiveCount = max(0, (int) $stats['total'] - (int) $stats['activated']);
        $loyaltyEnabled = ($loyalty['enabled'] ?? false) === true;
        $loyaltyMissing = (int) ($loyalty['missing'] ?? 0);
    @endphp

    <div class="admin-users-page">
        <header class="admin-users-hero admin-users-hero--team">
            <div class="admin-users-hero__main">
                <h1 class="admin-users-hero__title">
                    <i class="fa fa-users" aria-hidden="true"></i>
                    {{ trans('user::users.users') }}
                </h1>
                <p class="admin-users-hero__lead">{{ trans('user::users.index.lead') }}</p>
            </div>

            <div class="admin-users-hero__actions">
                @if ($loyaltyEnabled && auth()->user()?->hasAccess('admin.loyalty.members.index'))
                    <a href="{{ route('admin.loyalty.members.index') }}" class="btn btn-default admin-users-hero__btn">
                        <i class="fa fa-star" aria-hidden="true"></i>
                        {{ trans('user::users.index.loyalty_browse') }}
                    </a>
                @endif

                @if ($loyaltyEnabled && $loyaltyMissing > 0 && auth()->user()?->hasAccess('admin.loyalty.members.enroll'))
                    <form
                        method="POST"
                        action="{{ route('admin.users.enroll_loyalty') }}"
                        class="admin-users-hero__enroll-form"
                        onsubmit="return confirm(@json(trans('user::users.index.loyalty_enroll_confirm')))"
                    >
                        @csrf
                        <button type="submit" class="btn btn-primary admin-users-hero__btn">
                            <i class="fa fa-user-plus" aria-hidden="true"></i>
                            {{ trans('user::users.index.loyalty_enroll_button') }}
                        </button>
                    </form>
                @endif

                @hasAccess('admin.roles.index')
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-default admin-users-hero__btn">
                        <i class="fa fa-shield" aria-hidden="true"></i>
                        {{ trans('user::users.index.manage_roles') }}
                    </a>
                @endHasAccess

                @hasAccess('admin.users.create')
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary admin-users-hero__btn">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        {{ trans('admin::resource.create', ['resource' => trans('user::users.user')]) }}
                    </a>
                @endHasAccess
            </div>
        </header>

        @if ($loyaltyEnabled && $loyaltyMissing > 0)
            <div class="alert alert-info admin-users-loyalty-alert">
                <strong>{{ trans('user::users.index.loyalty_enroll_alert_title') }}</strong>
                <p>{{ trans('user::users.index.loyalty_enroll_alert_lead', ['count' => number_format($loyaltyMissing)]) }}</p>
            </div>
        @endif

        <div class="admin-users-stats">
            <div class="admin-users-stats__stat">
                <span class="admin-users-stats__icon admin-users-stats__icon--primary">
                    <i class="fa fa-users" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="admin-users-stats__label">{{ trans('user::users.index.stats_total') }}</span>
                    <strong class="admin-users-stats__value">{{ number_format($stats['total']) }}</strong>
                </div>
            </div>
            <div class="admin-users-stats__stat">
                <span class="admin-users-stats__icon admin-users-stats__icon--success">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="admin-users-stats__label">{{ trans('user::users.index.stats_active') }}</span>
                    <strong class="admin-users-stats__value">{{ number_format($stats['activated']) }}</strong>
                    @if ($inactiveCount > 0)
                        <span class="admin-users-stats__hint">
                            {{ trans('user::users.index.stats_active_hint', ['count' => number_format($inactiveCount)]) }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="admin-users-stats__stat">
                <span class="admin-users-stats__icon admin-users-stats__icon--info">
                    <i class="fa fa-sign-in" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="admin-users-stats__label">{{ trans('user::users.index.stats_recent_login') }}</span>
                    <strong class="admin-users-stats__value">{{ number_format($stats['recent_login']) }}</strong>
                    <span class="admin-users-stats__hint">{{ trans('user::users.index.stats_recent_login_hint') }}</span>
                </div>
            </div>
            <div class="admin-users-stats__stat">
                <span class="admin-users-stats__icon admin-users-stats__icon--purple">
                    <i class="fa fa-user-plus" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="admin-users-stats__label">{{ trans('user::users.index.stats_new_month') }}</span>
                    <strong class="admin-users-stats__value">{{ number_format($stats['new_this_month']) }}</strong>
                </div>
            </div>
        </div>

        @if ($role_breakdown->isNotEmpty())
            <div class="admin-users-role-chips">
                <span class="admin-users-role-chips__label">{{ trans('user::users.index.by_role') }}</span>
                @foreach ($role_breakdown as $role)
                    <span class="admin-users-role-chips__chip" title="{{ $role->name }}">
                        <i class="fa fa-shield" aria-hidden="true"></i>
                        {{ $role->name }}
                        <strong>{{ number_format($role->users_count) }}</strong>
                    </span>
                @endforeach
            </div>
        @endif

        <div class="admin-users-card admin-users-card--table admin-users-table-card">
            <div class="admin-users-card__head">
                <div class="admin-users-card__head-text">
                    <h2>
                        <i class="fa fa-list" aria-hidden="true"></i>
                        {{ trans('user::users.index.table_title') }}
                    </h2>
                    <p>{{ trans('user::users.index.table_lead') }}</p>
                </div>
                <div class="admin-users-table-card__search" id="admin-users-table-search"></div>
            </div>

            <div class="admin-users-card__body index-table" id="users-table">
                @component('admin::components.table', ['class' => 'admin-users-table'])
                    @slot('thead')
                        <tr>
                            @include('admin::partials.table.select_all', ['name' => 'users'])

                            <th data-sort>{{ trans('admin::admin.table.id') }}</th>
                            <th>{{ trans('user::users.index.column_user') }}</th>
                            <th>{{ trans('user::users.index.column_roles') }}</th>
                            @if ($loyaltyEnabled)
                                <th>{{ trans('user::users.index.column_loyalty_member') }}</th>
                            @endif
                            <th>{{ trans('user::users.index.column_status') }}</th>
                            <th>{{ trans('user::users.table.last_login') }}</th>
                            <th data-sort>{{ trans('admin::admin.table.created') }}</th>
                            <th class="text-right admin-users-table__col-actions">{{ trans('user::users.index.actions') }}</th>
                        </tr>
                    @endslot
                @endcomponent
            </div>
        </div>
    </div>
@endsection

@push('globals')
    @include('user::admin.partials.lang-globals')
@endpush

@push('scripts')
    <script type="module">
        keypressAction([
            { key: 'c', route: '{{ route('admin.users.create') }}' },
        ]);

        Mousetrap.bind('del', function () {
            $('.btn-delete').trigger('click');
        });

        Mousetrap.bind('backspace', function () {
            $('.btn-delete').trigger('click');
        });

        DataTable.set('#users-table .table', {
            routePrefix: 'users',
            routes: {
                table: 'table',
                edit: 'edit',
                destroy: 'destroy',
            },
        });

        new DataTable(
            '#users-table .table',
            {
                layout: {
                    topEnd: {
                        search: {
                            placeholder: trans('user::users.index.search_placeholder'),
                        },
                    },
                },
                columns: [
                    {
                        data: 'checkbox',
                        orderable: false,
                        searchable: false,
                        width: '3%',
                    },
                    {
                        data: 'id',
                        width: '5%',
                        title: @json(trans('admin::admin.table.id')),
                    },
                    {
                        data: 'user',
                        orderable: false,
                        searchable: false,
                        title: @json(trans('user::users.index.column_user')),
                    },
                    {
                        data: 'roles',
                        orderable: false,
                        searchable: false,
                        title: @json(trans('user::users.index.column_roles')),
                    },
                    @if ($loyaltyEnabled)
                    {
                        data: 'loyalty_member',
                        orderable: false,
                        searchable: false,
                        title: @json(trans('user::users.index.column_loyalty_member')),
                    },
                    @endif
                    {
                        data: 'status',
                        orderable: false,
                        searchable: false,
                        title: @json(trans('user::users.index.column_status')),
                    },
                    {
                        data: 'last_login',
                        name: 'last_login',
                        searchable: false,
                        title: @json(trans('user::users.table.last_login')),
                    },
                    {
                        data: 'created',
                        name: 'created_at',
                        title: @json(trans('admin::admin.table.created')),
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-right admin-users-table__col-actions',
                        title: @json(trans('user::users.index.actions')),
                    },
                    { data: 'first_name', name: 'first_name', visible: false, title: '' },
                    { data: 'last_name', name: 'last_name', visible: false, title: '' },
                    { data: 'email', name: 'email', visible: false, title: '' },
                ],
            },
            function () {
                const $container = this.element.closest('.dt-container');
                const $search = $container.find('.dt-search');
                const $mount = $('#admin-users-table-search');
                const $searchInput = $search.find('input');

                mountUsersSelectAllHeader(this.element);

                if ($mount.length && $search.length) {
                    $search.appendTo($mount);
                }

                $container.find('.dt-layout-row').first().find('.dt-layout-end').empty();

                const query = new URLSearchParams(window.location.search).get('search');

                if (query && $searchInput.length) {
                    $searchInput.val(query).trigger('input');
                }

                bindUsersBulkLoyaltyEnroll(this);
            }
        );

        function mountUsersSelectAllHeader($table) {
            const $headerCell = $table.find('thead th').first();

            if ($headerCell.find('.select-all').length) {
                return;
            }

            $headerCell.html(`
                <div class="checkbox bulk-select-cell">
                    <input type="checkbox" class="select-all" id="users-select-all" aria-label="@json(trans('user::users.index.select_all'))">
                    <label for="users-select-all"></label>
                </div>
            `);
        }

        function bindUsersBulkLoyaltyEnroll(dtInstance) {
            const config = @json([
                'canEnrollLoyalty' => $loyaltyEnabled && auth()->user()?->hasAccess('admin.loyalty.members.enroll'),
                'enrollBulkUrl' => url('admin/users/enroll-loyalty'),
            ]);

            if (!config.canEnrollLoyalty) {
                return;
            }

            const $table = dtInstance.element;
            const $length = $table.closest('.dt-container').find('.dt-length');

            if ($length.find('.btn-enroll-loyalty').length) {
                return;
            }

            const $btn = $(`
                <button type="button" class="btn btn-default btn-enroll-loyalty">
                    <i class="fa fa-star" aria-hidden="true"></i>
                    <span>${trans('user::users.index.loyalty_enroll_bulk_button')}</span>
                </button>
            `);

            $length.append($btn);

            $btn.on('click', function () {
                const checked = $table.find('.select-row:checked');

                if (!checked.length) {
                    if (typeof window.error === 'function') {
                        window.error(trans('user::users.index.loyalty_enroll_bulk_select_hint'));
                    }

                    return;
                }

                if (!confirm(trans('user::users.index.loyalty_enroll_bulk_confirm'))) {
                    return;
                }

                const ids = window.DataTable.getRowIds(checked);

                axios
                    .post(`${config.enrollBulkUrl}/${ids.join(',')}`)
                    .then((response) => {
                        window.DataTable.setSelectedIds('#users-table .table', []);
                        window.DataTable.reload('#users-table .table');

                        if (typeof window.success === 'function') {
                            window.success(response.data.message);
                        }
                    })
                    .catch((error) => {
                        const message =
                            error.response && error.response.data && error.response.data.message
                                ? error.response.data.message
                                : trans('admin::messages.something_went_wrong');

                        if (typeof window.error === 'function') {
                            window.error(message);
                        }
                    });
            });
        }
    </script>
@endpush

@push('styles')
    @vite(['modules/User/Resources/assets/admin/sass/main.scss'])
@endpush
