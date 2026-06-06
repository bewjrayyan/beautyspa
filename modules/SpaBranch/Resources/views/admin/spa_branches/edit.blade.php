@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('spabranch::spa_branches.spa_branch')]))
    @slot('subtitle', $spaBranch->name)

    <li><a href="{{ route('admin.spa_branches.index') }}">{{ trans('spabranch::spa_branches.spa_branches') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('spabranch::spa_branches.spa_branch')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.spa_branches.update', $spaBranch) }}" class="form-horizontal" id="spa-branch-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        {!! $tabs->render([
            'spaBranch' => $spaBranch,
            'branchBeauticianOptions' => $branchBeauticianOptions ?? [],
            'selectedBeauticianIds' => $selectedBeauticianIds ?? [],
        ]) !!}
    </form>
@endsection

@include('spabranch::admin.spa_branches.partials.shortcuts')
