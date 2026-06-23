<?php

namespace Modules\Meta\Support;

use Illuminate\Support\Str;

class OpenGraph
{
    public function __construct(
        public string $title,
        public string $description,
        public string $url,
        public string $type = 'website',
        public ?string $image = null,
        public ?string $imageAlt = null,
        public ?string $siteName = null,
        public ?string $priceAmount = null,
        public ?string $priceCurrency = null,
    ) {
        $this->siteName ??= (string) setting('store_name', config('app.name'));
        $this->imageAlt ??= $this->title;
    }


    public static function make(
        string $title,
        string $description,
        ?string $url = null,
        string $type = 'website',
        ?string $image = null,
        ?string $imageAlt = null,
        ?string $siteName = null,
        ?string $priceAmount = null,
        ?string $priceCurrency = null,
    ): self {
        return new self(
            title: $title,
            description: $description,
            url: $url ?: url()->current(),
            type: $type,
            image: self::absoluteUrl($image),
            imageAlt: $imageAlt,
            siteName: $siteName,
            priceAmount: $priceAmount,
            priceCurrency: $priceCurrency,
        );
    }


    public static function forStore(?string $logoPath = null): self
    {
        $title = trim((string) setting('store_tagline', ''));
        $title = $title !== '' ? $title : (string) setting('store_name', config('app.name'));

        $description = trim((string) setting('store_description', ''));
        $description = $description !== '' ? $description : $title;

        $image = $logoPath ?: storefront_favicon_url();

        return self::make(
            title: $title,
            description: Str::limit($description, 200, '…'),
            url: storefront_home_url(),
            image: $image,
            imageAlt: (string) setting('store_name', config('app.name')),
        );
    }


    public static function absoluteUrl(?string $url): ?string
    {
        return absolute_public_url($url);
    }


    public static function preferHttps(string $url): string
    {
        if (Str::startsWith($url, 'http://') && (request()->secure() || Str::startsWith((string) config('app.url'), 'https://'))) {
            return 'https://'.Str::after($url, 'http://');
        }

        return $url;
    }


    public function imageMimeType(): ?string
    {
        if (! $this->image) {
            return null;
        }

        $path = parse_url($this->image, PHP_URL_PATH) ?: $this->image;

        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => null,
        };
    }


    public function twitterCard(): string
    {
        return $this->image ? 'summary_large_image' : 'summary';
    }


    public function description(): string
    {
        return Str::limit(trim($this->description), 200, '…');
    }
}
