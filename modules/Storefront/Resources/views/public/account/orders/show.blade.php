@extends('storefront::public.account.layout')

@section('account_mobile_hero', true)

@section('title', trans('storefront::account.view_order.view_order') . ' #' . $order->id)

@section('account_breadcrumb')
    <li><a href="{{ route('account.orders.index') }}">{{ trans('storefront::account.pages.my_orders') }}</a></li>
    <li class="active">{{ trans('storefront::account.orders.view_order') }} #{{ $order->id }}</li>
@endsection

@section('panel')
    <div class="account-order-show">
        <header class="account-order-show__hero">
            <div class="account-order-show__hero-main">
                <a href="{{ route('account.orders.index') }}" class="account-order-show__back d-none d-lg-inline-flex">
                    <i class="las la-arrow-left"></i>
                    {{ trans('storefront::account.view_order.back_to_orders') }}
                </a>

                <h1 class="account-order-show__title">
                    {{ trans('storefront::account.view_order.view_order') }}
                    <span>#{{ $order->id }}</span>
                </h1>

                <p class="account-order-show__meta">
                    <i class="las la-calendar"></i>
                    {{ $order->created_at->format('l, d M Y · h:i A') }}
                </p>

                <div class="account-order-show__badges account-order-show__badges--mobile d-lg-none">
                    <span class="badge {{ order_status_badge_class($order->status) }}">
                        {{ $order->status() }}
                    </span>
                    <span class="badge {{ payment_status_badge_class($order->payment_status) }}">
                        {{ $order->paymentStatusLabel() }}
                    </span>
                </div>
            </div>

            <div class="account-order-show__hero-total">
                <span class="account-order-show__hero-total-label">{{ trans('storefront::account.view_order.total') }}</span>
                <span class="account-order-show__hero-total-value">
                    {{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                </span>

                <div class="account-order-show__badges account-order-show__badges--desktop d-none d-lg-flex">
                    <span class="badge {{ order_status_badge_class($order->status) }}">
                        {{ $order->status() }}
                    </span>
                    <span class="badge {{ payment_status_badge_class($order->payment_status) }}">
                        {{ $order->paymentStatusLabel() }}
                    </span>
                </div>
            </div>
        </header>

        <div class="account-order-show__layout">
            <main class="account-order-show__main">
                <div class="account-order-show__cards account-order-show__cards--addresses">
                    @include('storefront::public.account.orders.show.billing_address')
                    @include('storefront::public.account.orders.show.shipping_address')
                </div>

                <section class="account-order-show__section">
                    <h2 class="account-order-show__section-title">
                        <i class="las la-shopping-bag"></i>
                        {{ trans('storefront::account.view_order.items_ordered') }}
                    </h2>

                    @include('storefront::public.account.orders.show.items_ordered')
                    @include('storefront::public.account.orders.show.order_totals')
                </section>

                @include('storefront::public.account.orders.show.order_reviews')

                @include('storefront::public.account.orders.show.order_rewards')

                <section class="account-order-show__section account-order-show__section--payment d-lg-none">
                    <h2 class="account-order-show__section-title">
                        <i class="las la-credit-card"></i>
                        {{ trans('storefront::account.view_order.payment_details') }}
                    </h2>

                    @include('storefront::public.account.orders.show.payment_details', ['variant' => 'compact'])
                </section>
            </main>

            <aside class="account-order-show__sidebar">
                @include('storefront::public.account.orders.show.sidebar')
            </aside>
        </div>
    </div>
@endsection

@push('globals')
    <script>
        AestheticCart.langs['storefront::product.review_submitted'] = '{{ trans('storefront::product.review_submitted') }}';
    </script>

    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/orders/show/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/account/orders/show/main.js',
    ])
@endpush
