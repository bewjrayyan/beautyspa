@php
    $blogBackUrl = $blogPost->category?->url() ?? route('blog_posts.index');
@endphp

<header class="blog-mobile-header d-lg-none">
    <a
        href="{{ $blogBackUrl }}"
        class="blog-mobile-header__back"
        aria-label="{{ trans('storefront::blog.blog_posts.back_to_blog') }}"
    >
        <i class="las la-arrow-left"></i>
    </a>

    <p class="blog-mobile-header__label">{{ trans('storefront::blog.blog') }}</p>

    <a href="#blog-post-share" class="blog-mobile-header__action" aria-label="{{ trans('storefront::blog.blog_posts.share') }}">
        <i class="las la-share-alt"></i>
    </a>
</header>
