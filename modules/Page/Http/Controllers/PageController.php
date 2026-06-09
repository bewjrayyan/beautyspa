<?php

namespace Modules\Page\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Page\Entities\Page;
use Modules\Media\Entities\File;
use Modules\Product\Entities\Product;

class PageController
{
    /**
     * Display page for the slug.
     *
     * @param string $slug
     *
     * @return Response
     */
    public function show($slug)
    {
        $logo = File::findOrNew(storefront_header_logo_id())->path;
        $page = Page::where('slug', $slug)->firstOrFail();

        if ($slug === 'faq') {
            $faq = $this->immaSeriLarisFaqContent();

            return view('storefront::public.pages.faq', [
                'page' => $page,
                'logo' => $logo,
                'faqIntro' => $faq['intro'],
                'faqSections' => $faq['sections'],
                'faqCta' => $faq['cta'],
                'latestProducts' => $this->latestProductsForSidebar(),
            ]);
        }

        if ($slug === 'terms-conditions') {
            return $this->immaSeriLarisLegalView(
                $page,
                $logo,
                $this->immaSeriLarisTermsContent(),
                ['latestProducts' => $this->latestProductsForSidebar()]
            );
        }

        if ($slug === 'privacy-policy') {
            return $this->immaSeriLarisLegalView($page, $logo, $this->immaSeriLarisPrivacyContent());
        }

        if ($slug === 'about-us') {
            return view('storefront::public.pages.about', [
                'page' => $page,
                'logo' => $logo,
                'latestProducts' => $this->latestProductsForSidebar(),
            ]);
        }

        return view('storefront::public.pages.show', compact('page', 'logo'));
    }


    /**
     * FAQ content for IMMA Seri Laris (EN / MS).
     *
     * @return array<string, mixed>
     */
    private function immaSeriLarisFaqContent(): array
    {
        $faqConfig = config('imma_faq', []);
        $locale = array_key_exists(locale(), $faqConfig) ? locale() : 'en';
        $faq = $faqConfig[$locale] ?? $faqConfig['en'] ?? [];

        $urls = [
            '/products' => route('products.index'),
            '/contact' => route('contact.create'),
            '/account' => route('account.dashboard.index'),
        ];

        $sections = collect($faq['sections'] ?? [])->map(function (array $section) use ($urls) {
            return [
                'title' => $section['title'],
                'items' => collect($section['items'] ?? [])->map(function (array $item) use ($urls) {
                    return [
                        'question' => $item['question'],
                        'answer' => str_replace(array_keys($urls), array_values($urls), $item['answer']),
                    ];
                })->all(),
            ];
        })->values()->all();

        return [
            'intro' => $faq['intro'] ?? '',
            'sections' => $sections,
            'cta' => [
                'text' => $faq['cta_text'] ?? '',
                'contact' => $faq['cta_contact'] ?? 'Contact Us',
                'treatments' => $faq['cta_treatments'] ?? 'Browse Treatments',
            ],
        ];
    }


    /**
     * @return \Illuminate\Contracts\View\View
     */
    private function immaSeriLarisLegalView(Page $page, string $logo, array $content, array $extra = [])
    {
        return view('storefront::public.pages.terms', array_merge([
            'page' => $page,
            'logo' => $logo,
            'termsIntro' => $content['intro'],
            'termsUpdated' => $content['last_updated'],
            'termsSections' => $content['sections'],
            'termsFooter' => $content['footer'],
            'termsContactLabel' => $content['contact_label'],
        ], $extra));
    }


    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function latestProductsForSidebar()
    {
        return Product::forCard()
            ->take(5)
            ->latest()
            ->get()
            ->map
            ->clean();
    }


    /**
     * @return array<string, mixed>
     */
    private function immaSeriLarisLegalContent(string $configKey, string $footerEn, string $footerMs): array
    {
        $config = config($configKey, []);
        $locale = array_key_exists(locale(), $config) ? locale() : 'en';
        $data = $config[$locale] ?? $config['en'] ?? [];

        $urls = [
            '/contact' => route('contact.create'),
            '/privacy-policy' => localized_url(locale(), 'privacy-policy'),
            '/terms-conditions' => localized_url(locale(), 'terms-conditions'),
        ];

        $sections = collect($data['sections'] ?? [])->map(function (array $section) use ($urls) {
            return [
                'title' => $section['title'],
                'content' => str_replace(array_keys($urls), array_values($urls), $section['content']),
            ];
        })->values()->all();

        return [
            'intro' => $data['intro'] ?? '',
            'last_updated' => $data['last_updated'] ?? '',
            'sections' => $sections,
            'footer' => $locale === 'ms' ? $footerMs : $footerEn,
            'contact_label' => $locale === 'ms' ? 'Hubungi Kami' : 'Contact Us',
        ];
    }


    /**
     * @return array<string, mixed>
     */
    private function immaSeriLarisTermsContent(): array
    {
        return $this->immaSeriLarisLegalContent(
            'imma_terms',
            'IMMA Seri Laris — treatment, spa & aesthetic services at immaserilaris.com',
            'IMMA Seri Laris — perkhidmatan rawatan, spa & estetik di immaserilaris.com'
        );
    }


    /**
     * @return array<string, mixed>
     */
    private function immaSeriLarisPrivacyContent(): array
    {
        return $this->immaSeriLarisLegalContent(
            'imma_privacy',
            'IMMA Seri Laris — your privacy matters. immaserilaris.com',
            'IMMA Seri Laris — privasi anda penting. immaserilaris.com'
        );
    }
}
