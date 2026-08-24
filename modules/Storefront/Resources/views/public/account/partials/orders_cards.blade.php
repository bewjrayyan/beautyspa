<div class="account-order-cards d-xl-none">
    @foreach ($orders as $order)
        <a
            href="{{ route('account.orders.show', $order) }}"
            class="account-order-card"
            aria-label="{{ trans('storefront::account.orders.view_order_number', ['id' => $order->id]) }}"
        >
            <div class="account-order-card__top">
                <div class="account-order-card__id">
                    <span class="account-order-card__number">#{{ $order->id }}</span>
                    <span class="account-order-card__date">{{ $order->created_at->format('d M Y') }}</span>
                </div>

                <span class="account-order-card__total">
                    <small>{{ trans('storefront::account.orders.total') }}</small>
                    <strong>{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</strong>
                </span>
            </div>

            @if ($order->products->isNotEmpty())
                <div class="account-order-card__products">
                    <span class="account-order-card__section-label">{{ trans('storefront::account.orders.treatments') }}</span>
                    @foreach ($order->products->take(2) as $product)
                        <span class="account-order-card__product">{{ $product->name }}</span>
                    @endforeach

                    @if ($order->products->count() > 2)
                        <span class="account-order-card__more">
                            {{ trans('storefront::account.orders.more_items', ['count' => $order->products->count() - 2]) }}
                        </span>
                    @endif
                </div>
            @endif

            @if ($order->beautician || $order->spaBranch || $order->appointment_date || $order->appointment_time)
                <div class="account-order-card__appointment">
                    <span class="account-order-card__section-label">{{ trans('storefront::account.orders.appointment') }}</span>
                    @if ($order->spaBranch)
                        <span><i class="las la-store" aria-hidden="true"></i>{{ $order->spaBranch->name }}</span>
                    @endif

                    @if ($order->beautician)
                        <span><i class="las la-user-circle" aria-hidden="true"></i>{{ $order->beautician->name }}</span>
                    @endif

                    @if ($order->appointment_date || $order->appointment_time)
                        <span>
                            <i class="las la-calendar" aria-hidden="true"></i>
                            @if ($order->appointment_date)
                                {{ $order->appointment_date->format('d M Y') }}
                            @endif
                            @if ($order->appointment_time)
                                {{ $order->appointment_date ? ' · ' : '' }}{{ $order->displayAppointmentTime() }}
                            @endif
                        </span>
                    @endif
                </div>
            @endif

            <div class="account-order-card__status-grid">
                <div class="account-order-card__status">
                    <span class="account-order-card__status-label">{{ trans('storefront::account.orders.order_status') }}</span>
                    <span class="badge {{ order_status_badge_class($order->status) }}">
                        {{ $order->status() }}
                    </span>
                </div>

                <div class="account-order-card__status">
                    <span class="account-order-card__status-label">{{ trans('storefront::account.orders.payment_status') }}</span>
                    <span class="badge {{ payment_status_badge_class($order->payment_status) }}">
                        {{ $order->paymentStatusLabel() }}
                    </span>
                </div>

                @if (is_module_enabled('TreatmentReservation'))
                    @if ($order->treatmentBooking)
                        <div class="account-order-card__status">
                            <span class="account-order-card__status-label">{{ trans('storefront::account.orders.treatment_status') }}</span>
                            <span class="badge {{ treatment_status_badge_class($order->treatmentBooking->status) }}">
                                {{ $order->treatmentBooking->treatmentStatusLabel() }}
                            </span>
                        </div>
                    @endif
                @endif
            </div>

            <span class="account-order-card__action">
                {{ trans('storefront::account.orders.view_details') }}
                <i class="las la-arrow-right" aria-hidden="true"></i>
            </span>
        </a>
    @endforeach
</div>
