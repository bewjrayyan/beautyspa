@extends('storefront::public.layout')

@section('body_class')
    account-page
@endsection

@section('breadcrumb')
    @if (request()->routeIs('account.dashboard.index'))
        <li class="active">{{ trans('storefront::account.pages.my_account') }}</li>
    @else
        <li><a href="{{ route('account.dashboard.index') }}">{{ trans('storefront::account.pages.my_account') }}</a></li>
    @endif

    @yield('account_breadcrumb')
@endsection

@section('content')
    @php($accountUser = auth()->user())

    <section @class([
        'account-wrap',
        'account-wrap--dashboard' => request()->routeIs('account.dashboard.index'),
        'account-wrap--subpage' => ! request()->routeIs('account.dashboard.index'),
    ])>
        <div class="container">
            @if (request()->routeIs('account.dashboard.index'))
                <div class="account-mobile-hero d-lg-none">
                    <div class="account-mobile-hero__avatar">
                        @if ($accountUser->avatarUrl())
                            <img src="{{ $accountUser->avatarUrl() }}" alt="{{ $accountUser->full_name }}" width="56" height="56">
                        @else
                            <span>{{ $accountUser->initials }}</span>
                        @endif
                    </div>

                    <div class="account-mobile-hero__info">
                        <h2 class="account-mobile-hero__name">{{ $accountUser->full_name }}</h2>
                        <p class="account-mobile-hero__email">{{ $accountUser->email }}</p>
                    </div>

                    <a href="{{ route('account.profile.edit') }}" class="account-mobile-hero__edit" aria-label="{{ trans('storefront::account.dashboard.edit') }}">
                        <i class="las la-pen"></i>
                    </a>
                </div>
            @elseif (! request()->routeIs('account.orders.show', 'account.profile.edit', 'account.loyalty.index'))
                @include('storefront::public.account.partials.mobile_header')
            @endif

            <div class="account-wrap-inner">
                <aside class="account-left">
                    <div class="account-app-nav">
                        @include('storefront::public.account.partials.sidebar')
                    </div>
                </aside>

                <div class="account-right">
                    <div class="panel-wrap">
                        @yield('panel')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/main.scss'
    ])
@endpush
