@include('storefront::public.partials.sticky_search', [
    'searchPlacement' => 'hero',
    'searchAction' => storefront_route('products.index'),
    'searchPlaceholder' => trans('storefront::layouts.search_for_products'),
])
