@php
    $searchTitleLabel = $searchTitle ?? trans('storefront::blog.blog_posts.search');
@endphp

<div @class([
    'blog-search-section',
    'blog-search-section--hero' => ($searchPlacement ?? 'hero') === 'hero',
    'blog-search-section--sidebar' => ($searchPlacement ?? 'hero') === 'sidebar',
])>
    <h4 class="section-title">{{ $searchTitleLabel }}</h4>

    <form
        method="GET"
        action="{{ $searchAction }}"
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
                value="{{ $searchQuery ?? request('query') }}"
                placeholder="{{ $searchPlaceholder }}"
                autocomplete="off"
                enterkeyhint="search"
                aria-label="{{ $searchPlaceholder }}"
            >
        </label>

        <button type="submit" aria-label="{{ $searchTitleLabel }}">
            <i class="las la-arrow-right" aria-hidden="true"></i>
        </button>
    </form>
</div>
