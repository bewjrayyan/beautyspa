<nav class="account-app-menu" aria-label="{{ trans('storefront::account.pages.my_account') }}">
    <div class="account-app-menu__group">
        <ul class="account-sidebar list-inline d-flex flex-column">
            <li class="{{ request()->routeIs('account.dashboard.index') ? 'active' : '' }}">
                <a href="{{ route('account.dashboard.index') }}">
                    <span class="account-app-menu__icon account-app-menu__icon--dashboard" aria-hidden="true">
                        <i class="las la-tachometer-alt"></i>
                    </span>

                    <span class="account-app-menu__label">{{ trans('storefront::account.pages.dashboard') }}</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="account-app-menu__group">
        <p class="account-app-menu__heading d-lg-none">{{ trans('storefront::account.menu.orders_activity') }}</p>

        <ul class="account-sidebar list-inline d-flex flex-column">
            <li class="{{ request()->routeIs('account.orders.*') ? 'active' : '' }}">
                <a href="{{ route('account.orders.index') }}">
                    <span class="account-app-menu__icon account-app-menu__icon--orders" aria-hidden="true">
                        <i class="las la-cart-arrow-down"></i>
                    </span>

                    <span class="account-app-menu__label">{{ trans('storefront::account.pages.my_orders') }}</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('account.downloads.index') ? 'active' : '' }}">
                <a href="{{ route('account.downloads.index') }}">
                    <span class="account-app-menu__icon account-app-menu__icon--downloads" aria-hidden="true">
                        <i class="las la-download"></i>
                    </span>

                    <span class="account-app-menu__label">{{ trans('storefront::account.pages.my_downloads') }}</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('account.wishlist.index') ? 'active' : '' }}">
                <a href="{{ route('account.wishlist.index') }}">
                    <span class="account-app-menu__icon account-app-menu__icon--wishlist" aria-hidden="true">
                        <i class="lar la-heart"></i>
                    </span>

                    <span class="account-app-menu__label">{{ trans('storefront::account.pages.my_wishlist') }}</span>

                    <span class="account-app-menu__badge count" x-show="$store.wishlist.count > 0" x-text="$store.wishlist.count"></span>
                </a>
            </li>

            <li class="{{ request()->routeIs('account.reviews.index') ? 'active' : '' }}">
                <a href="{{ route('account.reviews.index') }}">
                    <span class="account-app-menu__icon account-app-menu__icon--reviews" aria-hidden="true">
                        <i class="las la-comment"></i>
                    </span>

                    <span class="account-app-menu__label">{{ trans('storefront::account.pages.my_reviews') }}</span>
                </a>
            </li>

            @if (app('modules')->isEnabled('TreatmentReservation'))
                <li class="{{ request()->routeIs('treatment_reservations.booking.*') ? 'active' : '' }}">
                    <a href="{{ route('treatment_reservations.booking.lookup') }}">
                        <span class="account-app-menu__icon account-app-menu__icon--appointments" aria-hidden="true">
                            <i class="las la-calendar-check"></i>
                        </span>

                        <span class="account-app-menu__label">{{ trans('treatmentreservation::public.nav_link') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>

    @if (app('modules')->isEnabled('Loyalty'))
        <div class="account-app-menu__group">
            <p class="account-app-menu__heading d-lg-none">{{ trans('storefront::account.menu.rewards') }}</p>

            <ul class="account-sidebar list-inline d-flex flex-column">
                <li class="{{ request()->routeIs('account.loyalty.index') ? 'active' : '' }}">
                    <a href="{{ route('account.loyalty.index') }}">
                        <span class="account-app-menu__icon account-app-menu__icon--loyalty" aria-hidden="true">
                            <i class="las la-star"></i>
                        </span>

                        <span class="account-app-menu__label">{{ trans('loyalty::account.title') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    @endif

    <div class="account-app-menu__group">
        <p class="account-app-menu__heading d-lg-none">{{ trans('storefront::account.menu.account_settings') }}</p>

        <ul class="account-sidebar list-inline d-flex flex-column">
            <li class="{{ request()->routeIs('account.addresses.index') ? 'active' : '' }}">
                <a href="{{ route('account.addresses.index') }}">
                    <span class="account-app-menu__icon account-app-menu__icon--addresses" aria-hidden="true">
                        <i class="las la-address-book"></i>
                    </span>

                    <span class="account-app-menu__label">{{ trans('storefront::account.pages.my_addresses') }}</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('account.profile.edit') ? 'active' : '' }}">
                <a href="{{ route('account.profile.edit') }}">
                    <span class="account-app-menu__icon account-app-menu__icon--profile" aria-hidden="true">
                        <i class="las la-user-circle"></i>
                    </span>

                    <span class="account-app-menu__label">{{ trans('storefront::account.pages.my_profile') }}</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="account-app-menu__group account-app-menu__group--logout">
        <ul class="account-sidebar list-inline d-flex flex-column">
            <li class="account-sidebar__logout">
                <a href="{{ route('logout') }}">
                    <span class="account-app-menu__icon account-app-menu__icon--logout" aria-hidden="true">
                        <i class="las la-sign-out-alt"></i>
                    </span>

                    <span class="account-app-menu__label">{{ trans('storefront::account.pages.logout') }}</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
