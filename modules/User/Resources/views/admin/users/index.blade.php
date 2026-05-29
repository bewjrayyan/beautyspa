@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('user::users.users'))

    <li class="active">{{ trans('user::users.users') }}</li>
@endcomponent

@section('content')
    <div class="admin-users-page">
        <header class="admin-users-hero">
            <div class="admin-users-hero__main">
                <h1 class="admin-users-hero__title">
                    <i class="fa fa-users" aria-hidden="true"></i>
                    {{ trans('user::users.users') }}
                </h1>
                <p class="admin-users-hero__lead">{{ trans('user::users.index.lead') }}</p>
            </div>

            <div class="admin-users-hero__actions">
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

        <div class="admin-users-card admin-users-card--table">
            <div class="admin-users-card__head">
                <div>
                    <h2>
                        <i class="fa fa-list" aria-hidden="true"></i>
                        {{ trans('user::users.index.table_title') }}
                    </h2>
                    <p>{{ trans('user::users.index.table_lead') }}</p>
                </div>
            </div>

            <div class="admin-users-card__body index-table" id="users-table">
                @component('admin::components.table', ['class' => 'admin-users-table'])
                    @slot('thead')
                        <tr>
                            @include('admin::partials.table.select_all')

                            <th data-sort>{{ trans('admin::admin.table.id') }}</th>
                            <th>{{ trans('user::users.index.column_user') }}</th>
                            <th>{{ trans('user::users.index.column_roles') }}</th>
                            <th>{{ trans('user::users.index.column_status') }}</th>
                            <th>{{ trans('user::users.table.last_login') }}</th>
                            <th data-sort>{{ trans('admin::admin.table.created') }}</th>
                        </tr>
                    @endslot
                @endcomponent
            </div>
        </div>
    </div>
@endsection

@include('user::admin.users.partials.shortcuts')

@push('scripts')
    <script type="module">
        keypressAction([
            { key: 'c', route: '{{ route('admin.users.create') }}' },
            { key: 'b', route: '{{ route('admin.users.index') }}' },
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

        new DataTable('#users-table .table', {
            columns: [
                {
                    data: 'checkbox',
                    orderable: false,
                    searchable: false,
                    width: '3%',
                    title: '',
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
                { data: 'first_name', name: 'first_name', visible: false, title: '' },
                { data: 'last_name', name: 'last_name', visible: false, title: '' },
                { data: 'email', name: 'email', visible: false, title: '' },
            ],
        });
    </script>
@endpush

@push('globals')
    <script>
        @foreach (array_keys(__('user::users.index')) as $indexKey)
            FleetCart.langs['user::users.index.{{ $indexKey }}'] = @json(__('user::users.index.' . $indexKey));
        @endforeach
    </script>

    @vite([
        'modules/User/Resources/assets/admin/sass/main.scss',
        'modules/User/Resources/assets/admin/js/main.js',
    ])
@endpush
