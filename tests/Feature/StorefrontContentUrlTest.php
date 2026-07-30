<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StorefrontContentUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://immaserilaris.com/v2');
    }

    #[Test]
    public function it_rehomes_a_legacy_localhost_link_to_the_current_install(): void
    {
        $this->assertSame(
            'https://immaserilaris.com/v2/en/products?featured=1#offers',
            storefront_content_url('http://localhost/fleetcart/en/products?featured=1#offers')
        );
    }

    #[Test]
    public function it_preserves_external_and_current_install_links(): void
    {
        $this->assertSame(
            'https://merchant.example/offers',
            storefront_content_url('https://merchant.example/offers')
        );

        $this->assertSame(
            'https://immaserilaris.com/v2/en/products',
            storefront_content_url('https://immaserilaris.com/v2/en/products')
        );
    }

    #[Test]
    public function it_rejects_unsafe_or_credential_bearing_links(): void
    {
        $this->assertNull(storefront_content_url('javascript:alert(1)'));
        $this->assertNull(storefront_content_url('https://user:secret@example.test/private'));
        $this->assertNull(storefront_content_url("https://example.test/\nheader"));
    }
}
