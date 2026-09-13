<?php

namespace Modules\Meta\Support;

use Artesaos\SEOTools\OpenGraph as SeoOpenGraph;
use Artesaos\SEOTools\SEOMeta;
use Artesaos\SEOTools\TwitterCards;
use Illuminate\Contracts\Container\Container;

class SeoToolsRenderer
{
    public function __construct(private Container $container)
    {
    }


    public function render(
        OpenGraph $metadata,
        string $robots = 'index, follow',
        ?string $documentTitle = null,
        array $productProperties = [],
    ): string {
        $metaTags = clone $this->container->make('seotools.metatags');
        $openGraph = clone $this->container->make('seotools.opengraph');
        $twitter = clone $this->container->make('seotools.twitter');

        $this->configureMetaTags($metaTags, $metadata, $robots, $documentTitle);
        $this->configureOpenGraph($openGraph, $metadata, $productProperties);
        $this->configureTwitter($twitter, $metadata);

        return implode(PHP_EOL, array_filter([
            $metaTags->generate(),
            $openGraph->generate(),
            $twitter->generate(),
        ]));
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
