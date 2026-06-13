@extends('admin::layout')

@section('title', trans('admin::resource.edit', ['resource' => trans('user::users.user')]))

@section('content_header')
    <div class="admin-users-header-bar">
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
                <li><a href="{{ route('admin.users.index') }}">{{ trans('user::users.users') }}</a></li>
                <li class="active">{{ $user->full_name }}</li>
            </ol>
        </nav>

        @include('user::admin.users.partials.edit-header-actions', ['user' => $user])
    </div>
@endsection

@section('content')
    <div class="admin-profile-page admin-profile-page--edit">
        @include('user::admin.users.partials.edit-hero', ['user' => $user])

        <form
            method="POST"
            action="{{ route('admin.users.update', $user) }}"
            class="form-horizontal"
            id="user-edit-form"
            data-admin-account-form
            enctype="multipart/form-data"
            novalidate
        >
            {{ csrf_field() }}
            {{ method_field('put') }}

            {!! $tabs->renderAccountLayout([
                'user' => $user,
                'profileUser' => $user,
                'loyaltyWallet' => $loyaltyWallet ?? null,
                'countries' => $countries ?? [],
                'profileAddress' => $profileAddress ?? null,
                'roles' => $roles ?? [],
            ]) !!}
        </form>
    </div>
@endsection

@include('user::admin.users.partials.shortcuts')

@push('globals')
    @include('user::admin.partials.lang-globals')

    @vite(array_filter([
        'modules/User/Resources/assets/admin/sass/main.scss',
        'modules/User/Resources/assets/admin/js/profileForm.js',
        'modules/User/Resources/assets/admin/js/main.js',
        app('modules')->isEnabled('Loyalty')
            ? 'modules/Loyalty/Resources/assets/admin/sass/main.scss'
            : null,
    ]))

    <script type="module">
        if (window.admin?.removeSubmitButtonOffsetOn) {
            window.admin.removeSubmitButtonOffsetOn(['#account', '#new_password', '#permissions']);
        }
    </script>
@endpush
