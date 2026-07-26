<?php

namespace Modules\Page\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Page\Entities\Page;
use Modules\Media\Entities\File;
use Modules\Product\Entities\Product;
use Modules\SpaBranch\Entities\SpaBranch;

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
            return $this->legalView($page, $logo, [
                'latestProducts' => $this->latestProductsForSidebar(),
            ]);
        }

        if ($slug === 'privacy-policy') {
            return $this->legalView($page, $logo);
        }

        if ($slug === 'about-us') {
            return view('storefront::public.pages.about', [
                'page' => $page,
                'logo' => $logo,
                'latestProducts' => $this->latestProductsForSidebar(),
                'spaBranches' => $this->activeSpaBranches(),
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
    private function legalView(Page $page, string $logo, array $extra = [])
    {
        return view('storefront::public.pages.terms', array_merge([
            'page' => $page,
            'logo' => $logo,
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
     * @return \Illuminate\Support\Collection<int, \Modules\SpaBranch\Entities\SpaBranch>
     */
    private function activeSpaBranches()
    {
        if (! app('modules')->isEnabled('SpaBranch')) {
            return collect();
        }

        return SpaBranch::activeForContact();
    }
}
