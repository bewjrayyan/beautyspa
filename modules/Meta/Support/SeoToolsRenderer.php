<?php

namespace Modules\Meta\Support;

use Artesaos\SEOTools\OpenGraph as SeoOpenGraph;
use Artesaos\SEOTools\SEOMeta;
use Artesaos\SEOTools\TwitterCards;
use Illuminate\Config\Repository as ConfigRepository;

class SeoToolsRenderer
{
    public function render(
        OpenGraph $metadata,
        string $robots = 'index, follow',
        ?string $documentTitle = null,
        array $productProperties = [],
    ): string {
        if (! $this->packageAvailable()) {
            return $this->renderFallback($metadata, $robots, $documentTitle, $productProperties);
        }

        $metaTags = new SEOMeta(new ConfigRepository(config('seotools.meta', [])));
        $openGraph = new SeoOpenGraph(config('seotools.opengraph', []));
        $twitter = new TwitterCards(config('seotools.twitter.defaults', []));

        $this->configureMetaTags($metaTags, $metadata, $robots, $documentTitle);
        $this->configureOpenGraph($openGraph, $metadata, $productProperties);
        $this->configureTwitter($twitter, $metadata);

        return implode(PHP_EOL, array_filter([
            $metaTags->generate(),
            $openGraph->generate(),
            $twitter->generate(),
        ]));
    }


    protected function packageAvailable(): bool
    {
        return class_exists(SEOMeta::class)
            && class_exists(SeoOpenGraph::class)
            && class_exists(TwitterCards::class);
    }


    private function renderFallback(
        OpenGraph $metadata,
        string $robots,
        ?string $documentTitle,
        array $productProperties,
    ): string {
        $tags = [
            '<title>'.$this->escape($documentTitle ?: $metadata->title).'</title>',
            $this->metaTag('name', 'description', $metadata->description()),
            $this->metaTag('name', 'robots', $this->normalizeRobots($robots)),
            '<link rel="canonical" href="'.$this->escape($metadata->url).'">',
            $this->metaTag('property', 'og:title', $metadata->title),
            $this->metaTag('property', 'og:description', $metadata->description()),
            $this->metaTag('property', 'og:url', $metadata->url),
            $this->metaTag('property', 'og:type', $metadata->type),
            $this->metaTag('property', 'og:site_name', (string) $metadata->siteName),
            $this->metaTag('property', 'og:locale', str_replace('-', '_', app()->getLocale())),
        ];

        if ($metadata->image) {
            $tags[] = $this->metaTag('property', 'og:image', $metadata->image);
            $tags[] = $this->metaTag('property', 'og:image:secure_url', $metadata->image);
            $tags[] = $this->metaTag('property', 'og:image:type', (string) $metadata->imageMimeType());
            $tags[] = $this->metaTag('property', 'og:image:alt', (string) $metadata->imageAlt);
        }

        if ($metadata->type === 'product') {
            foreach (array_filter(array_merge([
                'price:amount' => $metadata->priceAmount,
                'price:currency' => $metadata->priceCurrency,
            ], $productProperties), fn ($value) => $value !== null && $value !== '') as $property => $value) {
                $tags[] = $this->metaTag('property', 'product:'.$property, (string) $value);
            }
        }

        $tags[] = $this->metaTag('name', 'twitter:card', $metadata->twitterCard());
        $tags[] = $this->metaTag('name', 'twitter:title', $metadata->title);
        $tags[] = $this->metaTag('name', 'twitter:description', $metadata->description());
        $tags[] = $this->metaTag('name', 'twitter:url', $metadata->url);

        if ($metadata->image) {
            $tags[] = $this->metaTag('name', 'twitter:image', $metadata->image);
        }

        return implode(PHP_EOL, array_filter($tags));
    }


    private function metaTag(string $attribute, string $key, string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        return '<meta '.$attribute.'="'.$this->escape($key).'" content="'.$this->escape($value).'">';
    }


    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }


    private function configureMetaTags(
        SEOMeta $metaTags,
        OpenGraph $metadata,
        string $robots,
        ?string $documentTitle,
    ): void {
        $metaTags
            ->setTitle($documentTitle ?: $metadata->title, false)
            ->setDescription($metadata->description())
            ->setCanonical($metadata->url)
            ->setRobots($this->normalizeRobots($robots));
    }


    private function configureOpenGraph(
        SeoOpenGraph $openGraph,
        OpenGraph $metadata,
        array $productProperties,
    ): void {
        $openGraph
            ->setTitle($metadata->title)
            ->setDescription($metadata->description())
            ->setUrl($metadata->url)
            ->setType($metadata->type)
            ->setSiteName($metadata->siteName)
            ->addProperty('locale', str_replace('-', '_', app()->getLocale()));

        if ($metadata->image) {
            $openGraph->addImage($metadata->image, array_filter([
                'secure_url' => $metadata->image,
                'type' => $metadata->imageMimeType(),
                'alt' => $metadata->imageAlt,
            ]));
        }

        if ($metadata->type === 'product') {
            $openGraph->setProduct(array_filter(array_merge([
                'price:amount' => $metadata->priceAmount,
                'price:currency' => $metadata->priceCurrency,
            ], $productProperties), fn ($value) => $value !== null && $value !== ''));
        }
    }


    private function configureTwitter(TwitterCards $twitter, OpenGraph $metadata): void
    {
        $twitter
            ->setTitle($metadata->title)
            ->setDescription($metadata->description())
            ->setUrl($metadata->url)
            ->setType($metadata->twitterCard());

        if ($metadata->image) {
            $twitter->setImage($metadata->image);
        }
    }


    private function normalizeRobots(string $robots): string
    {
        return in_array($robots, ['index, follow', 'noindex, follow'], true)
            ? $robots
            : 'index, follow';
    }
}
