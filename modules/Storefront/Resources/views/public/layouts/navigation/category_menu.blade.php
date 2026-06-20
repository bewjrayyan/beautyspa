@php($categoryItems = $categoryMenu->menus())

<div
    x-data="{ open: false }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
    class="category-nav {{ request()->routeIs('home') ? 'show' : 'category-dropdown-menu' }}"
>
    <button
        type="button"
        class="category-nav-inner"
        @unless (request()->routeIs('home'))
            @click="open = !open"
            :aria-expanded="open"
        @else
            aria-expanded="true"
        @endunless
        aria-haspopup="true"
        aria-controls="category-dropdown-panel"
    >
        <span class="category-nav-label">
            <span class="category-nav-icon" aria-hidden="true">
                <i class="las la-list-ul"></i>
            </span>

            <span class="category-nav-text">{{ trans('storefront::layouts.all_categories_header') }}</span>
        </span>

        @unless (request()->routeIs('home'))
            <span class="category-nav-chevron" :class="{ 'is-open': open }" aria-hidden="true">
                <i class="las la-angle-down"></i>
            </span>
        @endunless
    </button>

    @if ($categoryItems->isNotEmpty())
        <div
            id="category-dropdown-panel"
            class="category-dropdown-wrap"
            :class="{ show: open }"
            role="menu"
        >
            <div class="category-dropdown">
                <div class="category-dropdown-header">
                    <span class="category-dropdown-title">{{ trans('storefront::layouts.browse_categories') }}</span>

                    <span class="category-dropdown-count">{{ $categoryItems->count() }}</span>
                </div>

                <div class="category-dropdown-body">
                    <ul class="list-inline mega-menu vertical-megamenu">
                        @foreach ($categoryItems as $menu)
                            @include('storefront::public.layouts.navigation.menu', ['type' => 'category_menu'])
                        @endforeach
                    </ul>
                </div>

                <div class="category-dropdown-footer">
                    <a
                        href="{{ storefront_route('categories.index') }}"
                        class="category-dropdown-cta"
                        title="{{ trans('storefront::layouts.all_categories') }}"
                    >
                        <span>{{ trans('storefront::layouts.all_categories') }}</span>
                        <i class="las la-th-list" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
