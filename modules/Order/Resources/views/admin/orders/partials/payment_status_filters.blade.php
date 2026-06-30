@php
    use Modules\Order\Entities\Order;

    $activePaymentStatus = (string) request('payment_status', '');
@endphp

<div class="orders-index__toolbar">
    <div class="orders-index__toolbar-intro">
        <span class="orders-index__toolbar-icon" aria-hidden="true">
            <i class="fa fa-credit-card"></i>
        </span>
        <span class="orders-index__toolbar-label">{{ trans('order::orders.filter_by_payment_status') }}</span>
    </div>

    <div
        class="orders-index__payment-filters"
        id="orders-payment-filters"
        role="group"
        aria-label="{{ trans('order::orders.filter_by_payment_status') }}"
    >
        <button
            type="button"
            class="orders-index__payment-filter {{ $activePaymentStatus === '' ? 'is-active' : '' }}"
            data-payment-status=""
            aria-pressed="{{ $activePaymentStatus === '' ? 'true' : 'false' }}"
        >
            <span class="orders-index__payment-filter-text">{{ trans('order::orders.filter_payment_status_all') }}</span>
            <span class="orders-index__payment-filter-count">{{ number_format($totalOrdersCount) }}</span>
        </button>

        @foreach (Order::paymentStatuses() as $status)
            @php
                $count = (int) ($paymentStatusCounts[$status] ?? 0);
                $isActive = $activePaymentStatus === $status;
            @endphp
            <button
                type="button"
                class="orders-index__payment-filter orders-index__payment-filter--{{ $status }} {{ $isActive ? 'is-active' : '' }}"
                data-payment-status="{{ $status }}"
                aria-pressed="{{ $isActive ? 'true' : 'false' }}"
            >
                <span class="orders-index__payment-filter-dot" aria-hidden="true"></span>
                <span class="orders-index__payment-filter-text">{{ trans('order::payment_statuses.' . $status) }}</span>
                <span class="orders-index__payment-filter-count">{{ number_format($count) }}</span>
            </button>
        @endforeach
    </div>
</div>
