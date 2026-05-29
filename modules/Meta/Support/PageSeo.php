<?php

namespace Modules\Meta\Support;

use Illuminate\Support\Str;
use Modules\Media\Entities\File;
use Modules\Page\Entities\Page;

class PageSeo
{
    public function __construct(
        protected Page $page,
        protected ?string $fallbackImageUrl = null,
    ) {
    }


    public static function for(Page $page, ?string $fallbackImageUrl = null): self
    {
        return new self($page, $fallbackImageUrl);
    }


    public function title(): string
    {
        $metaTitle = $this->translation()?->meta_title;

        return $metaTitle ?: (string) $this->page->name;
    }


    public function description(): string
    {
        $metaDescription = $this->translation()?->meta_description;

        if ($metaDescription) {
            return $metaDescription;
        }

        return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->page->body))), 160, '…');
    }


    public function robots(): string
    {
        return $this->translation()?->meta_robots ?: 'index, follow';
    }


    public function canonicalUrl(): string
    {
        return $this->page->url() !== '#' ? $this->page->url() : url()->current();
    }


    public function imageUrl(): ?string
    {
        $ogImageId = $this->translation()?->og_image_id;

        if ($ogImageId) {
            $path = File::find($ogImageId)?->path;

            if ($path) {
                return $this->absoluteUrl($path);
            }
        }

        return $this->fallbackImageUrl
            ? $this->absoluteUrl($this->fallbackImageUrl)
            : null;
    }


    public function twitterCard(): string
    {
        return $this->imageUrl() ? 'summary_large_image' : 'summary';
    }


    public function siteName(): string
    {
        return (string) setting('store_name', config('app.name'));
    }


    public function structuredData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $this->title(),
            'description' => $this->description(),
            'url' => $this->canonicalUrl(),
            'inLanguage' => locale(),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $this->siteName(),
                'url' => url('/'),
            ],
        ];

        if ($image = $this->imageUrl()) {
            $data['primaryImageOfPage'] = [
                '@type' => 'ImageObject',
                'url' => $image,
            ];
        }

        return $data;
    }


    protected function translation()
    {
        return $this->page->meta?->translate(locale(), false);
    }


    protected function absoluteUrl(string $url): string
    {
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url($url);
    }
}
