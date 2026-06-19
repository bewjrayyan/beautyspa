<ul class="account-sidebar list-inline d-flex flex-column">
    <li class="{{ request()->routeIs('account.dashboard.index') ? 'active' : '' }}">
        <a href="{{ route('account.dashboard.index') }}">
            <i class="las la-tachometer-alt"></i>

            {{ trans('storefront::account.pages.dashboard') }}
        </a>
    </li>

    <li class="{{ request()->routeIs('account.orders.*') ? 'active' : '' }}">
        <a href="{{ route('account.orders.index') }}">
            <i class="las la-cart-arrow-down"></i>

            {{ trans('storefront::account.pages.my_orders') }}
        </a>
    </li>

    <li class="{{ request()->routeIs('account.downloads.index') ? 'active' : '' }}">
        <a href="{{ route('account.downloads.index') }}">
            <i class="las la-download"></i>

            {{ trans('storefront::account.pages.my_downloads') }}
        </a>
    </li>

    <li class="{{ request()->routeIs('account.wishlist.index') ? 'active' : '' }}">
        <a href="{{ route('account.wishlist.index') }}">
            <i class="lar la-heart"></i>

            {{ trans('storefront::account.pages.my_wishlist') }}

            <span class="count" x-text="$store.wishlist.count"></span>
        </a>
    </li>

    <li class="{{ request()->routeIs('account.reviews.index') ? 'active' : '' }}">
        <a href="{{ route('account.reviews.index') }}">
            <i class="las la-comment"></i>

            {{ trans('storefront::account.pages.my_reviews') }}
        </a>
    </li>

    @if (app('modules')->isEnabled('TreatmentReservation'))
        <li class="{{ request()->routeIs('treatment_reservations.booking.*') ? 'active' : '' }}">
            <a href="{{ route('treatment_reservations.booking.lookup') }}">
                <i class="las la-calendar-check"></i>

                {{ trans('treatmentreservation::public.nav_link') }}
            </a>
        </li>
    @endif

    @if (app('modules')->isEnabled('Loyalty'))
        <li class="{{ request()->routeIs('account.loyalty.index') ? 'active' : '' }}">
            <a href="{{ route('account.loyalty.index') }}">
                <i class="las la-star"></i>

                {{ trans('loyalty::account.title') }}
            </a>
        </li>
    @endif

    <li class="{{ request()->routeIs('account.addresses.index') ? 'active' : '' }}">
        <a href="{{ route('account.addresses.index') }}">
            <i class="las la-address-book"></i>

            {{ trans('storefront::account.pages.my_addresses') }}
        </a>
    </li>

    <li class="{{ request()->routeIs('account.profile.edit') ? 'active' : '' }}">
        <a href="{{ route('account.profile.edit') }}">
            <i class="las la-user-circle"></i>

            {{ trans('storefront::account.pages.my_profile') }}
        </a>
    </li>

    <li class="account-sidebar__logout">
        <a href="{{ route('logout') }}">
            <i class="las la-sign-out-alt"></i>

            {{ trans('storefront::account.pages.logout') }}
        </a>
    </li>
</ul>
