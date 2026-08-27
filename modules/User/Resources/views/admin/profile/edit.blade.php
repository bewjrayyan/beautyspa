@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('user::users.profile'))

    <li class="active">{{ trans('user::users.profile') }}</li>
@endcomponent

@section('content')
    <div class="admin-profile-page">
        @include('user::admin.profile.partials.hero', ['user' => $profileUser])

        <form
            method="POST"
            action="{{ route('admin.profile.update') }}"
            class="form-horizontal"
            id="profile-form"
            data-admin-account-form
            enctype="multipart/form-data"
            novalidate
        >
            {{ csrf_field() }}
            {{ method_field('put') }}

            {!! $tabs->renderProfile([
                'profileUser' => $profileUser,
                'loyaltyWallet' => $loyaltyWallet ?? null,
                'countries' => $countries ?? [],
                'profileAddress' => $profileAddress ?? null,
            ]) !!}
        </form>
    </div>
@endsection

@push('globals')
    @vite(array_filter([
        'modules/User/Resources/assets/admin/sass/main.scss',
        'modules/User/Resources/assets/admin/js/profileForm.js',
        app('modules')->isEnabled('Loyalty')
            ? 'modules/Loyalty/Resources/assets/admin/sass/main.scss'
            : null,
    ]))

    <script type="module">
        if (window.admin?.removeSubmitButtonOffsetOn) {
            window.admin.removeSubmitButtonOffsetOn(['#account', '#newPassword']);
        }
    </script>
@endpush
