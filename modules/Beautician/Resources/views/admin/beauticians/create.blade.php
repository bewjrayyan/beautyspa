@extends('admin::layout')

@section('title', trans('admin::resource.create', ['resource' => trans('beautician::beauticians.beautician')]))

@section('content_header')
    <h3>{{ trans('admin::resource.create', ['resource' => trans('beautician::beauticians.beautician')]) }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li><a href="{{ route('admin.beauticians.index') }}">{{ trans('beautician::beauticians.beauticians') }}</a></li>
        <li class="active">{{ trans('admin::resource.create', ['resource' => trans('beautician::beauticians.beautician')]) }}</li>
    </ol>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.beauticians.store') }}" class="form-horizontal" id="beautician-form" novalidate>
        {{ csrf_field() }}

        @if (is_module_enabled('SpaBranch'))
            <input type="hidden" name="spa_branches_present" value="1">
        @endif

        {!! $tabs->render([
            'beautician' => $beautician,
            'adminUsers' => $adminUsers ?? [],
            'spaBranches' => $spaBranches ?? collect(),
            'selectedSpaBranchIds' => $selectedSpaBranchIds ?? [],
        ]) !!}
    </form>
@endsection

@push('globals')
    @vite([
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
    ])
@endpush
