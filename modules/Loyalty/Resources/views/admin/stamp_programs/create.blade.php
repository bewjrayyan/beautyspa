@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('loyalty::stamp_programs.program')]))

    <li><a href="{{ route('admin.loyalty.stamp_programs.index') }}">{{ trans('loyalty::stamp_programs.programs') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('loyalty::stamp_programs.program')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.loyalty.stamp_programs.store') }}">
        @csrf

        @include('loyalty::admin.stamp_programs.partials.form', ['program' => $program])

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-primary">
                    {{ trans('admin::admin.buttons.save') }}
                </button>
                <a href="{{ route('admin.loyalty.stamp_programs.index') }}" class="btn btn-default">
                    {{ trans('admin::admin.buttons.cancel') }}
                </a>
            </div>
        </div>
    </form>
@endsection

@push('styles')
    @vite(['modules/Loyalty/Resources/assets/admin/sass/main.scss'])
@endpush
