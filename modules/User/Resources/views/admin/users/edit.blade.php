@extends('admin::layout')

@section('title', trans('admin::resource.edit', ['resource' => trans('user::users.user')]))

@section('content_header')
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.users.index') }}">{{ trans('user::users.users') }}</a></li>
        <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('user::users.user')]) }}</li>
    </ol>
@endsection

@section('content')
    <div class="admin-profile-page">
        @include('user::admin.profile.partials.hero', ['user' => $user])

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
