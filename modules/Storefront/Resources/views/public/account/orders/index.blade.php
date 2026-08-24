@extends('storefront::public.account.layout')

@section('title', trans('storefront::account.pages.my_orders'))

@section('account_breadcrumb')
    <li class="active">{{ trans('storefront::account.pages.my_orders') }}</li>
@endsection

@section('panel')
    <div class="panel account-orders-panel">
        <div class="panel-header account-orders-header">
            <div class="account-orders-header__intro">
                <span class="account-orders-header__icon" aria-hidden="true">
                    <i class="las la-shopping-bag"></i>
                </span>

                <div>
                    <h4>{{ trans('storefront::account.pages.my_orders') }}</h4>
                    <p>{{ trans('storefront::account.orders.page_description') }}</p>
                </div>
            </div>

            <span class="account-orders-header__count">
                {{ trans_choice('storefront::account.orders.orders_count', $orders->total(), ['count' => number_format($orders->total())]) }}
            </span>
        </div>

        <div class="panel-body">
            @if ($orders->isEmpty())
                <div class="empty-message account-orders-empty">
                    <span class="account-orders-empty__icon" aria-hidden="true">
                        <i class="las la-receipt"></i>
                    </span>
                    <h3>{{ trans('storefront::account.orders.no_orders') }}</h3>
                    <p>{{ trans('storefront::account.orders.no_orders_description') }}</p>
                    <a href="{{ storefront_route('products.index') }}" class="btn btn-primary">
                        {{ trans('storefront::account.orders.browse_treatments') }}
                    </a>
                </div>
            @else
                @include('storefront::public.account.partials.orders_table')
            @endif
        </div>

        @if ($orders->hasPages())
            <div class="panel-footer account-orders-footer">
                {!! $orders->links() !!}
            </div>
        @endif
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/orders/index/main.scss',
    ])
@endpush
