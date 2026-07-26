@extends('storefront::public.layout')

@section('title', $page->name)

@push('meta')
    @include('meta::public.page_seo', ['page' => $page, 'fallbackImageUrl' => $logo])
@endpush

@section('content')
    <section class="custom-page-wrap imma-legal-wrap clearfix">
        <div class="container">
            <div class="imma-legal-layout">
                <div class="imma-legal-main">
                    <div class="imma-legal-header">
                        <h1>{{ $page->name }}</h1>
                        <p class="imma-legal-updated">
                            {{ trans('page::pages.legal.public_updated', ['date' => $page->updated_at?->format('d M Y')]) }}
                        </p>
                    </div>

                    <div class="imma-legal-content custom-page-content">
                        {!! clean_html($page->body) !!}
                    </div>

                    <div class="imma-legal-footer">
                        <p>{{ trans('page::pages.legal.public_footer') }}</p>
                        <a href="{{ route('contact.create') }}" class="btn btn-primary">
                            {{ trans('page::pages.legal.contact') }}
                        </a>
                    </div>
                </div>

                <div class="imma-legal-sidebar d-none d-lg-block">
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
