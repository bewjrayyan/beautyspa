@include('storefront::public.account.partials.orders_cards', ['orders' => $orders])

<div class="table-responsive d-none d-lg-block">
    <table class="table table-borderless my-orders-table">
        <thead>
        <tr>
            <th>{{ trans('storefront::account.orders.order') }}</th>
            <th class="my-orders-table__col-treatments">{{ trans('storefront::account.orders.treatments') }}</th>
            <th>{{ trans('storefront::account.orders.appointment') }}</th>
            <th>{{ trans('storefront::account.orders.order_status') }}</th>
            <th>{{ trans('storefront::account.orders.payment_status') }}</th>
            @if (is_module_enabled('TreatmentReservation'))
                <th>{{ trans('storefront::account.orders.treatment_status') }}</th>
            @endif
            <th>{{ trans('storefront::account.orders.total') }}</th>
            <th>{{ trans('storefront::account.action') }}</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($orders as $order)
            <tr>
                <td class="my-orders-table__order">
                    <a href="{{ route('account.orders.show', $order) }}" class="my-orders-table__order-link">
                        #{{ $order->id }}
                    </a>
                    <span class="my-orders-table__order-date">
                        {{ $order->created_at->format('d M Y') }}
                    </span>
                </td>
                <td class="my-orders-table__treatments">
                    @if ($order->products->isNotEmpty())
                        <div class="my-orders-table__product-list">
                            @foreach ($order->products->take(2) as $product)
                                <div class="my-orders-table__product-name">{{ $product->name }}</div>
                            @endforeach
                        </div>
                        @if ($order->products->count() > 2)
                            <span class="my-orders-table__more">
                                {{ trans('storefront::account.orders.more_items', ['count' => $order->products->count() - 2]) }}
                            </span>
                        @endif
                    @else
                        <span class="my-orders-table__muted">—</span>
                    @endif
                </td>
                <td class="my-orders-table__appointment">
                    @if ($order->beautician || $order->spaBranch || $order->appointment_date || $order->appointment_time)
                        <div class="my-orders-table__appointment-inner">
                            @if ($order->spaBranch)
                                <span class="my-orders-table__branch">
                                    <i class="las la-store" aria-hidden="true"></i>
                                    {{ $order->spaBranch->name }}
                                </span>
                            @endif
                            @if ($order->beautician)
                                <span class="my-orders-table__beautician">
                                    <i class="las la-user-circle" aria-hidden="true"></i>
                                    {{ $order->beautician->name }}
                                </span>
                            @endif
                            @if ($order->appointment_date || $order->appointment_time)
                                <span class="my-orders-table__appt-time">
                                    <i class="las la-calendar" aria-hidden="true"></i>
                                    @if ($order->appointment_date)
                                        {{ $order->appointment_date->format('d M Y') }}
                                    @endif
                                    @if ($order->appointment_time)
                                        {{ $order->appointment_date ? ' · ' : '' }}{{ $order->appointment_time }}
                                    @endif
                                </span>
                            @endif
                        </div>
                    @else
                        <span class="my-orders-table__muted">{{ trans('storefront::account.orders.no_appointment') }}</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ order_status_badge_class($order->status) }}">
                        {{ $order->status() }}
                    </span>
                </td>
                <td>
                    <span class="badge {{ payment_status_badge_class($order->payment_status) }}">
                        {{ $order->paymentStatusLabel() }}
                    </span>
                </td>
                @if (is_module_enabled('TreatmentReservation'))
                    <td>
                        @include('storefront::public.account.partials.order_treatment_status', ['order' => $order])
                    </td>
                @endif
                <td class="my-orders-table__total">
                    {{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                </td>
                <td>
                    <a href="{{ route('account.orders.show', $order) }}"
                       title="{{ trans('storefront::account.orders.view_order') }}" class="btn btn-view">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path
                                d="M14.3623 7.3635C14.565 7.6477 14.6663 7.78983 14.6663 8.00016C14.6663 8.2105 14.565 8.35263 14.3623 8.63683C13.4516 9.9139 11.1258 12.6668 7.99967 12.6668C4.87353 12.6668 2.54774 9.9139 1.63703 8.63683C1.43435 8.35263 1.33301 8.2105 1.33301 8.00016C1.33301 7.78983 1.43435 7.6477 1.63703 7.3635C2.54774 6.08646 4.87353 3.3335 7.99967 3.3335C11.1258 3.3335 13.4516 6.08646 14.3623 7.3635Z"
                                stroke="white" stroke-width="1"/>
                            <path
                                d="M10 8C10 6.8954 9.1046 6 8 6C6.8954 6 6 6.8954 6 8C6 9.1046 6.8954 10 8 10C9.1046 10 10 9.1046 10 8Z"
                                stroke="white" stroke-width="1"/>
                        </svg>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
