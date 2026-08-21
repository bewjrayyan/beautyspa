@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('product::products.product')]))
    @slot('subtitle', $product->name)

    <li><a href="{{ route('admin.products.index') }}">{{ trans('product::products.products') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('product::products.product')]) }}</li>
@endcomponent

@section('content')
    <div id="app" v-cloak></div>
@endsection


@include('product::admin.products.partials.shortcuts')
@include('product::admin.products.partials.scripts')

@push('globals')
    <script>
        AestheticCart.data['product'] = {!! $product_resource !!};
        AestheticCart.data['up-sell-products'] = @json($product->upSellProducts ?? []);
        AestheticCart.data['cross-sell-products'] = @json($product->crossSellProducts ?? []);
        AestheticCart.data['related-products'] = @json($product->relatedProducts ?? []);
        AestheticCart.data['storefront_product_url_template'] = @json(localized_url(locale(), route('products.show', ['slug' => '__SLUG__'])));
    </script>

    @vite([
        'modules/Product/Resources/assets/admin/sass/main.scss',
        'modules/Product/Resources/assets/admin/js/main.js',
        'modules/Attribute/Resources/assets/admin/sass/main.scss',
        'modules/Variation/Resources/assets/admin/sass/main.scss',
        'modules/Option/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
    ])
@endpush
