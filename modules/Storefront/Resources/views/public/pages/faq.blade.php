@extends('storefront::public.layout')

@section('title', $page->name)

@push('meta')
    @include('meta::public.page_seo', ['page' => $page, 'fallbackImageUrl' => $logo])
@endpush

@section('content')
    <section class="custom-page-wrap imma-faq-wrap clearfix">
        <div class="container">
            <div class="imma-faq-layout">
                <div class="imma-faq-main">
                    <div class="imma-faq-header">
                        <h1>{{ $page->name }}</h1>
                        <p class="imma-faq-intro">{{ $faqIntro }}</p>
                    </div>

                    <div class="imma-faq" x-data="{ active: null }">
                        @foreach ($faqSections ?? [] as $sectionIndex => $section)
                            <div class="imma-faq-section">
                                <h2 class="imma-faq-section-title">{{ $section['title'] }}</h2>

                                <div class="imma-faq-list">
                                    @foreach ($section['items'] as $itemIndex => $item)
                                        @php($key = "{$sectionIndex}-{$itemIndex}")

                                        <div
                                            class="imma-faq-item"
                                            :class="{ 'is-open': active === '{{ $key }}' }"
                                        >
                                            <button
                                                type="button"
                                                class="imma-faq-question"
                                                @click="active = active === '{{ $key }}' ? null : '{{ $key }}'"
                                                :aria-expanded="active === '{{ $key }}'"
                                            >
                                                <span>{{ $item['question'] }}</span>
                                                <i class="las la-angle-down imma-faq-icon"></i>
                                            </button>

                                            <div
                                                class="imma-faq-answer"
                                                x-show="active === '{{ $key }}'"
                                                x-cloak
                                            >
                                                {!! clean_html($item['answer']) !!}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="imma-faq-cta">
                        <p>{{ $faqCta['text'] }}</p>
                        <a href="{{ route('contact.create') }}" class="btn btn-primary">
                            {{ $faqCta['contact'] }}
                        </a>
                        <a href="{{ storefront_route('products.index') }}" class="btn btn-default">
                            {{ $faqCta['treatments'] }}
                        </a>
                    </div>
                </div>

                <div class="imma-faq-sidebar d-none d-lg-block">
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
