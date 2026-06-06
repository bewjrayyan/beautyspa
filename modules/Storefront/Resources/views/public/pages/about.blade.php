@extends('storefront::public.layout')

@section('title', $page->name)

@push('meta')
    @include('meta::public.page_seo', ['page' => $page, 'fallbackImageUrl' => $logo])
@endpush

@section('content')
    <section class="custom-page-wrap imma-about-page clearfix">
        <div class="container">
            <div class="imma-about-layout">
                <div class="imma-about-main custom-page-content">
                    {!! clean_html($page->body) !!}
                </div>

                <div class="imma-about-sidebar d-none d-lg-block">
                    @include('storefront::public.partials.latest_products_sidebar')
                </div>
            </div>
        </div>
    </section>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/custom-page/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/custom-page/main.js',
    ])
@endpush
