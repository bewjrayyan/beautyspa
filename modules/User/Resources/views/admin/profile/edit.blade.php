@extends('admin::layout')

@section('title', trans('user::users.profile'))

@section('content_header')
@endsection

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
