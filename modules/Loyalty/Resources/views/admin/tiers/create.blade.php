@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('loyalty::tiers.tier')]))

    <li><a href="{{ route('admin.loyalty.tiers.index') }}">{{ trans('loyalty::tiers.tiers') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('loyalty::tiers.tier')]) }}</li>
@endcomponent

@section('content')
    @include('loyalty::admin.tiers.partials.form-page', [
        'tier' => $tier,
        'currencySymbol' => $currencySymbol,
        'isEdit' => false,
        'formAction' => route('admin.loyalty.tiers.store'),
        'formMethod' => null,
    ])
@endsection

@push('styles')
    @vite(['modules/Loyalty/Resources/assets/admin/sass/main.scss'])
@endpush
