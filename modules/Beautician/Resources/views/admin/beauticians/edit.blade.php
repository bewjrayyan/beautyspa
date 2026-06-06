@extends('admin::layout')

@section('title', trans('admin::resource.edit', ['resource' => trans('beautician::beauticians.beautician')]))

@section('content_header')
    <h3>{{ $beautician->name }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li><a href="{{ route('admin.beauticians.index') }}">{{ trans('beautician::beauticians.beauticians') }}</a></li>
        <li class="active">{{ trans('beautician::beauticians.form.edit_profile') }}</li>
    </ol>
@endsection

@section('content')
    @if ($credentials = session('beautician_portal_credentials'))
        <div class="alert alert-success">
            @if (! empty($credentials['password']))
                {{ trans('beautician::beauticians.form.portal_credentials_created', $credentials) }}
            @elseif (! empty($credentials['password_updated']))
                {{ trans('beautician::beauticians.form.portal_password_updated', ['email' => $credentials['email']]) }}
            @elseif (! empty($credentials['password_set']) || ! empty($credentials['created']))
                {{ trans('beautician::beauticians.form.portal_account_created', ['email' => $credentials['email']]) }}
            @endif
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.beauticians.update', $beautician) }}"
        class="form-horizontal"
        id="beautician-form"
        novalidate
    >
        {{ csrf_field() }}
        {{ method_field('put') }}

        @if (is_module_enabled('SpaBranch'))
            <input type="hidden" name="spa_branches_present" value="1">
        @endif

        {!! $tabs->render([
            'beautician' => $beautician,
            'adminUsers' => $adminUsers,
            'scheduleStats' => $scheduleStats ?? null,
            'spaBranches' => $spaBranches ?? collect(),
            'selectedSpaBranchIds' => $selectedSpaBranchIds ?? [],
        ]) !!}
    </form>
@endsection

@push('globals')
    @vite([
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
        'modules/TreatmentReservation/Resources/assets/admin/sass/main.scss',
        'modules/TreatmentReservation/Resources/assets/admin/js/main.js',
    ])
@endpush
