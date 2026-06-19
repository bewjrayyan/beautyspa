<header class="account-mobile-header d-lg-none">
    <a href="{{ route('account.dashboard.index') }}" class="account-mobile-header__back" aria-label="{{ trans('storefront::account.pages.my_account') }}">
        <i class="las la-arrow-left"></i>
    </a>

    <h1 class="account-mobile-header__title">@yield('title')</h1>
</header>
