<?php

namespace Tests\Unit\Meta;

use Modules\Meta\Support\OpenGraph;
use Modules\Meta\Support\SeoToolsRenderer;
use Tests\TestCase;

class SeoToolsRendererTest extends TestCase
{
    public function test_it_generates_complete_product_metadata_without_json_ld_duplicates(): void
    {
        $metadata = new OpenGraph(
            title: 'RoyalLift',
            description: 'Premium aesthetic treatment.',
            url: 'https://example.com/products/royallift',
            type: 'product',
            image: 'https://example.com/images/royallift.webp',
            imageAlt: 'RoyalLift treatment',
            siteName: 'IMMA Seri Laris',
            priceAmount: '6050.00',
            priceCurrency: 'MYR',
        );

        $html = app(SeoToolsRenderer::class)->render(
            metadata: $metadata,
            robots: 'noindex, follow',
            documentTitle: 'RoyalLift | IMMA Seri Laris',
            productProperties: [
                'brand' => 'IMMA Seri Laris',
                'availability' => 'in stock',
                'condition' => 'new',
            ],
        );

        $this->assertStringContainsString('<title>RoyalLift | IMMA Seri Laris</title>', $html);
        $this->assertStringContainsString('name="robots" content="noindex, follow"', $html);
        $this->assertStringContainsString('rel="canonical" href="https://example.com/products/royallift"', $html);
        $this->assertStringContainsString('property="og:type" content="product"', $html);
        $this->assertStringContainsString('property="product:price:amount" content="6050.00"', $html);
        $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $html);
        $this->assertStringNotContainsString('application/ld+json', $html);
        $this->assertSame(1, substr_count($html, '<title>'));
    }


    public function test_rendering_does_not_leak_metadata_between_products(): void
    {
        $renderer = app(SeoToolsRenderer::class);

        $renderer->render(new OpenGraph(
            title: 'First product',
            description: 'First description',
            url: 'https://example.com/products/first',
            image: 'https://example.com/first.jpg',
            siteName: 'Store',
        ));

        $html = $renderer->render(new OpenGraph(
            title: 'Second product',
            description: 'Second description',
            url: 'https://example.com/products/second',
            siteName: 'Store',
        ));

        $this->assertStringContainsString('Second product', $html);
        $this->assertStringNotContainsString('First product', $html);
        $this->assertStringNotContainsString('first.jpg', $html);
    }


    public function test_rendering_does_not_require_seotools_container_aliases(): void
    {
        foreach (['seotools.metatags', 'seotools.opengraph', 'seotools.twitter'] as $alias) {
            $this->app->offsetUnset($alias);
            $this->assertFalse($this->app->bound($alias));
        }

        $html = app(SeoToolsRenderer::class)->render(new OpenGraph(
            title: 'Container-safe product',
            description: 'Metadata renders without package container aliases.',
            url: 'https://example.com/products/container-safe',
            siteName: 'Store',
        ));

        $this->assertStringContainsString('<title>Container-safe product</title>', $html);
        $this->assertStringContainsString('property="og:title" content="Container-safe product"', $html);
        $this->assertStringContainsString('name="twitter:title" content="Container-safe product"', $html);
    }


    public function test_rendering_falls_back_when_seotools_package_is_unavailable(): void
    {
        $renderer = new class extends SeoToolsRenderer
        {
            protected function packageAvailable(): bool
            {
                return false;
            }
        };

        $html = $renderer->render(new OpenGraph(
            title: 'Production-safe product',
            description: 'Metadata remains available when package discovery cache is stale.',
            url: 'https://example.com/products/production-safe',
            type: 'product',
            priceAmount: '80.00',
            priceCurrency: 'MYR',
            siteName: 'Store',
        ));

        $this->assertStringContainsString('<title>Production-safe product</title>', $html);
        $this->assertStringContainsString('property="og:title" content="Production-safe product"', $html);
        $this->assertStringContainsString('property="product:price:amount" content="80.00"', $html);
        $this->assertStringContainsString('name="twitter:title" content="Production-safe product"', $html);
    }
}
