<section class="bottom-navigation-wrap d-lg-none" aria-label="{{ trans('storefront::layouts.navigation') }}">
    <nav class="bottom-navigation-bar">
        <ul class="bottom-navigation-items">
            <li>
                <a
                    href="{{ route('home') }}"
                    class="bottom-navigation-item {{ request()->routeIs('home') ? 'active' : '' }}"
                >
                    <span class="bottom-navigation-item__icon" aria-hidden="true">
                        <i class="las la-home"></i>
                    </span>
                    <span class="bottom-navigation-item__label">{{ trans('storefront::layouts.home') }}</span>
                </a>
            </li>

            <li>
                <a
                    href="{{ route('compare.index') }}"
                    class="bottom-navigation-item compare {{ request()->routeIs('compare.index') ? 'active' : '' }}"
                >
                    <span class="bottom-navigation-item__icon" aria-hidden="true">
                        <i class="las la-exchange-alt"></i>
                    </span>
                    <span class="bottom-navigation-item__label">{{ trans('storefront::layouts.compare') }}</span>
                    <span class="count" x-text="$store.compare.count">{{ $compareCount }}</span>
                </a>
            </li>

            <li>
                <a
                    href="{{ storefront_route('categories.index') }}"
                    class="bottom-navigation-item {{ request()->routeIs('categories.index') ? 'active' : '' }}"
                >
                    <span class="bottom-navigation-item__icon" aria-hidden="true">
                        <i class="las la-th-large"></i>
                    </span>
                    <span class="bottom-navigation-item__label">{{ trans('storefront::layouts.categories') }}</span>
                </a>
            </li>

            <li>
                <a
                    href="{{ route('cart.index') }}"
                    class="bottom-navigation-item bottom-navigation-cart {{ request()->routeIs('cart.index') ? 'active' : '' }}"
                    aria-label="{{ trans('storefront::layouts.my_cart') }}"
                    @click.prevent="$store.layout.openSidebarCart($event)"
                >
                    <span class="bottom-navigation-item__icon" aria-hidden="true">
                        <i class="las la-shopping-bag"></i>
                    </span>
                    <span class="bottom-navigation-item__label">{{ trans('storefront::layouts.cart') }}</span>
                    <span class="count" x-text="$store.cart.quantity">{{ $cartQuantity }}</span>
                </a>
            </li>

            <li>
                <a
                    href="{{ auth()->check() ? route('account.dashboard.index') : route('login') }}"
                    class="bottom-navigation-item {{ request()->routeIs('login') || request()->routeIs('account.*') ? 'active' : '' }}"
                >
                    <span class="bottom-navigation-item__icon" aria-hidden="true">
                        <i class="las la-user"></i>
                    </span>
                    <span class="bottom-navigation-item__label">{{ trans('storefront::layouts.account') }}</span>
                </a>
            </li>
        </ul>
    </nav>
</section>
