@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('shipping::shipping_classes.shipping_class')]))
    @slot('subtitle', $shippingClass->name)

    <li><a href="{{ route('admin.shipping_classes.index') }}">{{ trans('shipping::shipping_classes.shipping_classes') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('shipping::shipping_classes.shipping_class')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.shipping_classes.update', $shippingClass) }}" class="form-horizontal" id="shipping-class-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        {!! $tabs->render(compact('shippingClass')) !!}
    </form>
@endsection

@include('shipping::admin.shipping_classes.partials.shortcuts')
