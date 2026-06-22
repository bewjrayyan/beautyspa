@include('storefront::public.partials.sticky_search', [
    'searchPlacement' => $searchPlacement ?? 'hero',
    'searchTitle' => trans('storefront::blog.blog_posts.search'),
    'searchAction' => storefront_route('blog.search'),
    'searchPlaceholder' => trans('storefront::blog.blog_posts.search_blog_posts'),
])
