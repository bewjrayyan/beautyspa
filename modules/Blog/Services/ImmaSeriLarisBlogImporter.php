<?php

namespace Modules\Blog\Services;

use DOMDocument;
use DOMXPath;
use finfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Blog\Entities\BlogCategory;
use Modules\Blog\Entities\BlogPost;
use Modules\Blog\Entities\BlogTag;
use Modules\Media\Entities\File;
use Modules\User\Entities\User;

class ImmaSeriLarisBlogImporter
{
    private array $imageCache = [];

    public function import(string $url, bool $force = false): BlogPost
    {
        $url = rtrim($url, '/') . '/';
        $html = $this->fetchHtml($url);
        $parsed = $this->parsePost($html, $url);

        $existing = BlogPost::withoutGlobalScope('published')
            ->where('slug', $parsed['slug'])
            ->first();

        if ($existing && ! $force) {
            throw new \RuntimeException("Blog post already exists: {$parsed['slug']} (ID {$existing->id}).");
        }

        if ($existing) {
            $post = $existing;
            $post->load('translations');
        } else {
            $post = new BlogPost([
                'slug' => $parsed['slug'],
                'user_id' => User::query()->value('id'),
                'publish_status' => BlogPost::PUBLISHED,
            ]);
        }

        $post->blog_category_id = $this->resolveCategoryId($parsed['slug']);
        $post->publish_status = BlogPost::PUBLISHED;
        $post->user_id = User::query()->value('id');

        $post->save();

        $this->persistSlug($post, $parsed['slug']);

        foreach (supported_locale_keys() as $locale) {
            DB::table('blog_post_translations')->updateOrInsert(
                [
                    'blog_post_id' => $post->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $parsed['title'],
                    'description' => $parsed['description'],
                ]
            );
        }

        $post->unsetRelation('translations');
        $post->saveMetaData($parsed['meta']);

        if ($parsed['featured_image_url']) {
            $fileId = $this->downloadImage($parsed['featured_image_url']);

            if ($fileId) {
                $post->syncFiles(['featured_image' => [$fileId]]);
            }
        }

        $tagIds = [];

        foreach ($parsed['tags'] as $tagName) {
            $tagIds[] = $this->ensureTag($tagName);
        }

        $post->tags()->sync($tagIds);

        return $post->fresh(['tags', 'category', 'files']);
    }

    private function fetchHtml(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: AestheticCart-Blog-Importer/1.0\r\n",
                'timeout' => 30,
            ],
        ]);

        $html = @file_get_contents($url, false, $context);

        if ($html === false || trim($html) === '') {
            throw new \RuntimeException("Could not fetch URL: {$url}");
        }

        return $html;
    }

    private function parsePost(string $html, string $url): array
    {
        $slug = basename(rtrim(parse_url($url, PHP_URL_PATH) ?: '', '/')) ?: 'blog-post';

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        $title = $this->firstNodeText($xpath, '//h1[contains(@class,"entry-title")]')
            ?: $this->metaContent($html, 'og:title')
            ?: 'Blog Post';

        $title = html_entity_decode(trim(preg_replace('/\s*[-|].*Imma Serilaris.*/i', '', $title) ?? $title), ENT_QUOTES, 'UTF-8');

        $description = $this->extractEntryContent($xpath);

        if ($description === '') {
            throw new \RuntimeException("Could not extract blog body from: {$url}");
        }

        $featuredImage = $this->metaContent($html, 'og:image');
        $categoryName = $this->extractCategoryName($xpath) ?: 'Uncategorized @ms';
        $tags = $this->extractTagNames($xpath);

        if ($tags === []) {
            $tags = $this->defaultTagsForSlug($slug);
        }

        return [
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'featured_image_url' => $featuredImage ? $this->normalizeImageUrl($featuredImage) : null,
            'category_name' => $categoryName,
            'tags' => $tags,
            'meta' => [
                'meta_title' => $title . ' | Imma Serilaris',
                'meta_description' => Str::limit(strip_tags($description), 160, ''),
            ],
        ];
    }

    private function extractEntryContent(DOMXPath $xpath): string
    {
        $queries = [
            '//*[contains(@class,"entry-content")]',
            '//div[contains(concat(" ", normalize-space(@class), " "), " desc ")]',
            '//*[contains(@class,"elementor-widget-theme-post-content")]//*[contains(@class,"elementor-widget-container")]',
        ];

        foreach ($queries as $query) {
            $html = $this->htmlFromContentNode($xpath, $query);

            if ($this->isSubstantialPostHtml($html)) {
                return $this->cleanPostHtml($html);
            }
        }

        return '';
    }

    private function htmlFromContentNode(DOMXPath $xpath, string $query): string
    {
        $nodes = $xpath->query($query);

        if (! $nodes || $nodes->length === 0) {
            return '';
        }

        $node = $nodes->item(0);
        $document = $node->ownerDocument;
        $html = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeName === 'div' && str_contains($child->getAttribute('class') ?? '', 'sharedaddy')) {
                continue;
            }

            $html .= $document->saveHTML($child);
        }

        return trim($html);
    }

    private function isSubstantialPostHtml(string $html): bool
    {
        return strlen(trim(strip_tags($html))) >= 400;
    }

    private function cleanPostHtml(string $html): string
    {
        $html = preg_replace('/<h2[^>]*>\s*Share this:.*?<\/h2>/is', '', $html) ?? $html;
        $html = preg_replace('/<div[^>]*class="[^"]*sharedaddy[^"]*"[^>]*>.*?<\/div>/is', '', $html) ?? $html;
        $html = preg_replace('/Also Read:.*?(?=<h[23]|<p|<strong|$)/is', '', $html) ?? $html;

        return trim($html);
    }

    private function persistSlug(BlogPost $post, string $slug): void
    {
        if ($slug === '') {
            return;
        }

        DB::table('blog_posts')->where('id', $post->id)->update(['slug' => $slug]);
        $post->slug = $slug;
    }

    private function extractCategoryName(DOMXPath $xpath): string
    {
        $nodes = $xpath->query('//a[contains(@rel,"category") or contains(@class,"category")]');

        if (! $nodes) {
            return '';
        }

        for ($i = $nodes->length - 1; $i >= 0; $i--) {
            $text = trim($nodes->item($i)->textContent);

            if ($text !== '' && strlen($text) < 80) {
                return $text;
            }
        }

        return '';
    }

    private function extractTagNames(DOMXPath $xpath): array
    {
        $tags = [];
        $nodes = $xpath->query('//a[contains(@rel,"tag")]');

        if (! $nodes) {
            return $tags;
        }

        foreach ($nodes as $node) {
            $text = trim($node->textContent);

            if ($text !== '' && strlen($text) < 60 && ! in_array($text, $tags, true)) {
                $tags[] = $text;
            }
        }

        return $tags;
    }

    private function metaContent(string $html, string $property): ?string
    {
        if (preg_match('/property="' . preg_quote($property, '/') . '"\s+content="([^"]+)"/i', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
        }

        return null;
    }

    private function firstNodeText(DOMXPath $xpath, string $query): string
    {
        $nodes = $xpath->query($query);

        return $nodes && $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }

    private function defaultTagsForSlug(string $slug): array
    {
        return match ($slug) {
            'manfaat-aura-diamond-ad' => ['Aura Diamond', 'Drip', 'Paper Bag'],
            'manfaat-lumina-luxe' => ['Lumina Luxe', 'Drip', 'Whitening'],
            'manfaat-pure-booster-vitamin-c' => ['Pure Booster', 'Vitamin C', 'Drip'],
            'manfaat-snow-pearl-untuk-kulit' => ['Snow Pearl', 'Drip', 'Skincare'],
            'kekerapan-pengambilan-drip-menjaga-kesihatan-dan-kecantikan-anda' => ['Drip', 'Tips', 'Kekerapan'],
            'panduan-penjagaan-breastfiller-buttfiller-selepas-rawatan' => ['Breastfiller', 'Buttfiller', 'Tips'],
            default => [],
        };
    }

    private function resolveCategoryId(string $postSlug): int
    {
        $sync = app(TreatmentBlogCategorySync::class);
        $categorySlug = $sync->categorySlugForPost($postSlug) ?? 'aesthetic-estetik';

        if (! $sync->categoryIdForSlug($categorySlug)) {
            $sync->sync();
        }

        return $sync->categoryIdForSlug($categorySlug)
            ?? BlogCategory::query()->value('id');
    }

    private function ensureTag(string $name): int
    {
        $slug = Str::slug($name) ?: Str::random(8);

        $tag = BlogTag::where('slug', $slug)->first();

        if ($tag) {
            return $tag->id;
        }

        $tag = BlogTag::create(['slug' => $slug]);

        foreach (supported_locale_keys() as $locale) {
            $tag->translateOrNew($locale)->name = $name;
        }

        $tag->save();

        return $tag->id;
    }

    private function normalizeImageUrl(string $url): string
    {
        $url = html_entity_decode(trim($url), ENT_QUOTES, 'UTF-8');

        if (preg_match('#^https?://i\d+\.wp\.com/(.+?)(?:\?|$)#i', $url, $m)) {
            return 'https://' . preg_replace('/-\d+x\d+(\.[a-z0-9]+)$/i', '$1', $m[1]);
        }

        return preg_replace('/-\d+x\d+(\.[a-z0-9]+)$/i', '$1', $url) ?? $url;
    }

    private function downloadImage(string $url): ?int
    {
        $url = $this->normalizeImageUrl($url);

        if (isset($this->imageCache[$url])) {
            return $this->imageCache[$url];
        }

        $content = @file_get_contents($url);

        if ($content === false) {
            return null;
        }

        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        $extension = $pathInfo['extension'] ?? 'jpg';
        $filename = 'imma-blog-' . Str::slug($pathInfo['filename'] ?? Str::random(8)) . '.' . $extension;
        $storagePath = 'media/' . $filename;

        if (! Storage::disk(config('filesystems.default'))->put($storagePath, $content)) {
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content);

        $file = File::create([
            'user_id' => User::query()->value('id') ?? 1,
            'disk' => config('filesystems.default'),
            'filename' => substr($filename, 0, 255),
            'path' => $storagePath,
            'extension' => $extension,
            'mime' => $mime ?: 'image/jpeg',
            'size' => strlen($content),
        ]);

        return $this->imageCache[$url] = $file->id;
    }

}
