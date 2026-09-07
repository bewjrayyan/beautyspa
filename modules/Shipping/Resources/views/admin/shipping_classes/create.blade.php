@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('shipping::shipping_classes.shipping_class')]))

    <li><a href="{{ route('admin.shipping_classes.index') }}">{{ trans('shipping::shipping_classes.shipping_classes') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('shipping::shipping_classes.shipping_class')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.shipping_classes.store') }}" class="form-horizontal" id="shipping-class-create-form" novalidate>
        {{ csrf_field() }}

        {!! $tabs->render(compact('shippingClass')) !!}
    </form>
@endsection

@include('shipping::admin.shipping_classes.partials.shortcuts')
