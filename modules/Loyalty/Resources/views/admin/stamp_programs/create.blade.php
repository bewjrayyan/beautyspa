@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('loyalty::stamp_programs.program')]))

    <li><a href="{{ route('admin.loyalty.stamp_programs.index') }}">{{ trans('loyalty::stamp_programs.programs') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('loyalty::stamp_programs.program')]) }}</li>
@endcomponent

@section('content')
    @include('loyalty::admin.stamp_programs.partials.form-page', [
        'program' => $program,
        'eligibleSelection' => $eligibleSelection ?? ['category_ids' => [], 'products' => []],
        'categories' => $categories ?? [],
        'isEdit' => false,
        'formAction' => route('admin.loyalty.stamp_programs.store'),
    ])
@endsection

@push('styles')
    @vite(['modules/Loyalty/Resources/assets/admin/sass/main.scss'])
@endpush
