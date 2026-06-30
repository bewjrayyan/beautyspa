@extends('storefront::public.layout')

@section('title', $page->name)

@push('meta')
    @include('meta::public.page_seo', ['page' => $page, 'fallbackImageUrl' => $logo])
@endpush

@section('content')
    @php
        $spaBranches = $spaBranches ?? collect();
        $aboutBody = clean_html($page->body);
        $branchesSection = view('storefront::public.pages.partials.about_branches', [
            'spaBranches' => $spaBranches,
        ])->render();

        if (str_contains($aboutBody, '<!--IMMA_SPA_BRANCHES-->')) {
            $aboutBody = str_replace('<!--IMMA_SPA_BRANCHES-->', $branchesSection, $aboutBody);
        } elseif ($spaBranches->isNotEmpty()) {
            $aboutBody = preg_replace(
                '/(<section class="imma-about-section imma-about-cta")/',
                $branchesSection . '$1',
                $aboutBody,
                1
            ) ?? ($aboutBody . $branchesSection);
        }
    @endphp

    <section class="custom-page-wrap imma-about-page clearfix">
        <div class="container">
            <div class="imma-about-layout imma-about-layout--full">
                <div class="imma-about-main custom-page-content">
                    {!! $aboutBody !!}
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
