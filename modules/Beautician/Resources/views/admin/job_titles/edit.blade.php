@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('beautician::job_titles.job_title')]))

    <li><a href="{{ route('admin.beautician_job_titles.index') }}">{{ trans('beautician::job_titles.job_titles') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('beautician::job_titles.job_title')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.beautician_job_titles.update', $beauticianJobTitle) }}" class="form-horizontal" id="beautician-job-title-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        {!! $tabs->render(compact('beauticianJobTitle')) !!}
    </form>
@endsection
