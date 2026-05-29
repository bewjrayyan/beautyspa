@extends('storefront::public.layout')

@section('title', $page->name)

@push('meta')
    @include('meta::public.page_seo', ['page' => $page, 'fallbackImageUrl' => $logo])
@endpush

@section('content') 
    <section class="custom-page-wrap clearfix">
        <div class="container">
            <div class="custom-page-content clearfix">
                {!! clean_html($page->body) !!}
            </div>
        </div>
    </section>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/custom-page/main.scss',
    ])
@endpush