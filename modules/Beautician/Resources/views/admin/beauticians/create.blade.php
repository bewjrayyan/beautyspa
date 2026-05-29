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
    <form method="POST" action="{{ route('admin.beauticians.store') }}" class="form-horizontal" novalidate>
        {{ csrf_field() }}

        {!! $tabs->render([
            'beautician' => $beautician,
            'adminUsers' => $adminUsers ?? [],
        ]) !!}
    </form>
@endsection

@push('globals')
    @vite([
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
    ])
@endpush
