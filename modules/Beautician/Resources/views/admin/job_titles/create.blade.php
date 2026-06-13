@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('beautician::job_titles.job_title')]))

    <li><a href="{{ route('admin.beautician_job_titles.index') }}">{{ trans('beautician::job_titles.job_titles') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('beautician::job_titles.job_title')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.beautician_job_titles.store') }}" class="form-horizontal" id="beautician-job-title-create-form" novalidate>
        {{ csrf_field() }}

        {!! $tabs->render(compact('beauticianJobTitle')) !!}
    </form>
@endsection
