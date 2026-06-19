@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('loyalty::stamp_programs.program')]))
    @slot('subtitle', $program->name)

    <li><a href="{{ route('admin.loyalty.stamp_programs.index') }}">{{ trans('loyalty::stamp_programs.programs') }}</a></li>
    <li class="active">{{ $program->name }}</li>
@endcomponent

@section('content')
    @include('loyalty::admin.stamp_programs.partials.form-page', [
        'program' => $program,
        'eligibleSelection' => $eligibleSelection ?? ['category_ids' => [], 'products' => []],
        'categories' => $categories ?? [],
        'isEdit' => true,
        'formAction' => route('admin.loyalty.stamp_programs.update', $program),
        'formMethod' => 'put',
    ])
@endsection

@push('styles')
    @vite(['modules/Loyalty/Resources/assets/admin/sass/main.scss'])
@endpush
