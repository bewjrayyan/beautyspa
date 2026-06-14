<?php

namespace Modules\Storefront\Http\ViewComposers;

use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Blog\Entities\BlogPost;
use Modules\Meta\Support\OpenGraph;

class BlogPostShowComposer
{
    public function compose(View $view): void
    {
        $blogPost = $view->getData()['blogPost'] ?? null;

        if (! $blogPost instanceof BlogPost) {
            return;
        }

        $title = $blogPost->meta->meta_title ?: $blogPost->title;
        $description = $blogPost->meta->meta_description
            ?: Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $blogPost->body))), 200, '…');

        $view->with([
            'openGraph' => OpenGraph::make(
                title: $title,
                description: $description,
                url: url()->current(),
                type: 'article',
                image: $blogPost->featured_image->path ?? null,
                imageAlt: $title,
            ),
        ]);
    }
}
