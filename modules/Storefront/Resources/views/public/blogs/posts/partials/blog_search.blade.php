<div @class([
    'blog-search-section',
    'blog-search-section--hero' => ($searchPlacement ?? 'sidebar') === 'hero',
    'blog-search-section--sidebar' => ($searchPlacement ?? 'sidebar') === 'sidebar',
])>
    <h4 class="section-title">{{ trans('storefront::blog.blog_posts.search') }}</h4>

    <form
        method="GET"
        action="{{ storefront_route('blog.search') }}"
        class="blog-post-search"
        role="search"
    >
        <label class="blog-post-search__field">
            <span class="blog-post-search__icon" aria-hidden="true">
                <i class="las la-search"></i>
            </span>

            <input
                type="search"
                name="query"
                value="{{ request('query') }}"
                placeholder="{{ trans('storefront::blog.blog_posts.search_blog_posts') }}"
                autocomplete="off"
                enterkeyhint="search"
                aria-label="{{ trans('storefront::blog.blog_posts.search_blog_posts') }}"
            >
        </label>

        <button type="submit" aria-label="{{ trans('storefront::blog.blog_posts.search') }}">
            <i class="las la-arrow-right" aria-hidden="true"></i>
        </button>
    </form>
</div>
