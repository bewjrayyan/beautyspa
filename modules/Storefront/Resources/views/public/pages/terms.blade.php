@extends('storefront::public.layout')

@section('title', $page->name)

@push('meta')
    @include('meta::public.page_seo', ['page' => $page, 'fallbackImageUrl' => $logo])
@endpush

@section('content')
    <section class="custom-page-wrap imma-legal-wrap clearfix">
        <div class="container">
            <div class="imma-legal-header">
                <h1>{{ $page->name }}</h1>
                <p class="imma-legal-intro">{{ $termsIntro }}</p>
                <p class="imma-legal-updated">{{ $termsUpdated }}</p>
            </div>

            <div class="imma-legal-content custom-page-content">
                @foreach ($termsSections ?? [] as $section)
                    <article class="imma-legal-section">
                        <h2>{{ $section['title'] }}</h2>
                        {!! clean_html($section['content']) !!}
                    </article>
                @endforeach
            </div>

            <div class="imma-legal-footer">
                <p>{{ $termsFooter }}</p>
                <a href="{{ route('contact.create') }}" class="btn btn-primary">{{ $termsContactLabel }}</a>
            </div>
        </div>
    </section>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/custom-page/main.scss',
    ])
@endpush
