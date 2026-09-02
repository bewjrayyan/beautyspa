@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('product::products.products'))

    <li class="active">{{ trans('product::products.products') }}</li>
@endcomponent

@section('content')
    <div class="box box-primary products-index">
        <div class="box-header with-border products-index__head">
            <h3 class="box-title">{{ trans('product::products.products') }}</h3>
            <div class="box-tools pull-right">
                @hasAccess('admin.products.create')
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-actions btn-create">
                        {{ trans('admin::resource.create', ['resource' => trans('product::products.product')]) }}
                    </a>
                @endHasAccess
            </div>
        </div>

        @include('product::admin.products.partials.filters')

        <div class="box-body index-table" id="products-table">
            @component('admin::components.table')
                @slot('thead')
                    @include('product::admin.products.partials.thead', ['name' => 'products-index'])
                @endslot
            @endcomponent
        </div>
    </div>
@endsection

@push('styles')
    @vite(['modules/Product/Resources/assets/admin/sass/main.scss'])
@endpush

@push('scripts')
    @include('product::admin.products.partials.index-scripts')
@endpush
