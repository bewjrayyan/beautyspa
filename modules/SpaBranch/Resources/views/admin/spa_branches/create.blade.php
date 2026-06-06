@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('spabranch::spa_branches.spa_branch')]))

    <li><a href="{{ route('admin.spa_branches.index') }}">{{ trans('spabranch::spa_branches.spa_branches') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('spabranch::spa_branches.spa_branch')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.spa_branches.store') }}" class="form-horizontal" id="spa-branch-create-form" novalidate>
        {{ csrf_field() }}

        {!! $tabs->render([
            'spaBranch' => $spaBranch,
            'branchBeauticianOptions' => $branchBeauticianOptions ?? [],
            'selectedBeauticianIds' => $selectedBeauticianIds ?? [],
        ]) !!}
    </form>
@endsection

@include('spabranch::admin.spa_branches.partials.shortcuts')
