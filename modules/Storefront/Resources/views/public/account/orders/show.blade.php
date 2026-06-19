@extends('storefront::public.account.layout')

@section('account_mobile_hero', true)

@section('title', trans('storefront::account.view_order.view_order'))

@section('account_breadcrumb')
    <li><a href="{{ route('account.orders.index') }}">{{ trans('storefront::account.pages.my_orders') }}</a></li>
    <li class="active">{{ trans('storefront::account.orders.view_order') }} #{{ $order->id }}</li>
@endsection

@section('panel')
    <div class="account-order-show">
        <header class="account-order-show__hero">
            <div class="account-order-show__hero-main">
                <a href="{{ route('account.orders.index') }}" class="account-order-show__back">
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
            </div>

            <div class="account-order-show__hero-total">
                <span class="account-order-show__hero-total-label">{{ trans('storefront::account.view_order.total') }}</span>
                <span class="account-order-show__hero-total-value">
                    {{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                </span>

                <div class="account-order-show__badges">
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
            </main>

            <aside class="account-order-show__sidebar">
                @include('storefront::public.account.orders.show.sidebar')
            </aside>
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/orders/show/main.scss',
    ])
@endpush
