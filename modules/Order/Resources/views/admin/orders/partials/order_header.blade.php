@php
    $customerProfileUrl = $order->customer
        ? route('admin.users.edit', $order->customer)
        : null;

    $hasCustomerIdentity = $order->customer_id
        || filled($order->customer_email)
        || filled($order->customer_phone);

    $customerVisitLabel = $customerVisitLabel
        ?? (class_exists(\Modules\TreatmentReservation\Support\CustomerVisitLabel::class)
            ? \Modules\TreatmentReservation\Support\CustomerVisitLabel::forOrder($order)
            : null);

    $purchaseCount = $hasCustomerIdentity ? $order->customerPurchaseCount() : 0;
    $purchaseOrdinal = $hasCustomerIdentity ? $order->customerPurchaseOrdinal() : 0;

    $customerDob = $order->customer?->date_of_birth;
    $customerAge = $order->customer?->age();
    $hasBirthday = $customerDob !== null && $customerAge !== null;
@endphp

<div class="order-show__hero">
    <div class="order-show__workspace-bar">
        <a href="{{ route('admin.orders.index') }}" class="order-show__back-link">
            <i class="fa fa-arrow-left" aria-hidden="true"></i>
            {{ trans('order::orders.back_to_orders') }}
        </a>
        <span class="order-show__updated-at">
            {{ trans('order::orders.last_updated', ['time' => $order->updated_at->format('d M Y, H:i')]) }}
        </span>
    </div>

    <div class="order-show__hero-top">
        <section class="order-show__crm" aria-label="{{ trans('order::orders.order_summary') }}">
            <div class="order-show__crm-row">
                @include('order::admin.orders.partials.customer_avatar', [
                    'order' => $order,
                    'customerProfileUrl' => $customerProfileUrl,
                ])

                <div class="order-show__crm-body">
                    <div class="order-show__crm-head">
                        <div class="order-show__crm-title-wrap">
                            <h2 class="order-show__customer-name">
                                @if ($customerProfileUrl)
                                    @hasAccess('admin.users.edit')
                                        <a href="{{ $customerProfileUrl }}" class="order-show__customer-name-link">
                                            <span>{{ $order->customer_full_name }}</span>
                                        </a>
                                    @else
                                        {{ $order->customer_full_name }}
                                    @endHasAccess
                                @else
                                    {{ $order->customer_full_name }}
                                @endif
                            </h2>
                            <span class="order-show__crm-muted">
                                · {{ trans('order::orders.order') }} #{{ $order->id }}
                                · {{ $order->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <div class="order-show__crm-status" aria-label="{{ trans('order::orders.status_pipeline') }}">
                            <div class="order-show__crm-status-item">
                                <span class="order-show__crm-status-label">{{ trans('order::orders.order_status_badge') }}:</span>
                                <span
                                    class="order-show__crm-pill"
                                    id="order-status-badge"
                                    data-status-type="order"
                                    data-status="{{ $order->status }}"
                                    data-title-template="{{ trans('order::orders.order_status_title', ['status' => '__STATUS__']) }}"
                                    title="{{ trans('order::orders.order_status_title', ['status' => $order->status()]) }}"
                                >
                                    <span>{{ $order->status() }}</span>
                                </span>
                            </div>
                            <div class="order-show__crm-status-item">
                                <span class="order-show__crm-status-label">{{ trans('order::orders.payment_status_badge') }}:</span>
                                <span
                                    class="order-show__crm-pill"
                                    id="order-payment-status-badge"
                                    data-status-type="payment"
                                    data-status="{{ $order->payment_status }}"
                                    data-title-template="{{ trans('order::orders.payment_status_title', ['status' => '__STATUS__']) }}"
                                    title="{{ trans('order::orders.payment_status_title', ['status' => $order->paymentStatusLabel()]) }}"
                                >
                                    <span>{{ $order->paymentStatusLabel() }}</span>
                                </span>
                            </div>
                            @if (!empty($treatmentBooking))
                                <div class="order-show__crm-status-item">
                                    <span class="order-show__crm-status-label">{{ trans('order::orders.treatment_status_badge') }}:</span>
                                    <span
                                        class="order-show__crm-pill"
                                        id="order-treatment-status-badge"
                                        data-status-type="treatment"
                                        data-status="{{ $treatmentBooking->status }}"
                                        data-title-template="{{ trans('order::orders.treatment_status_title', ['status' => '__STATUS__']) }}"
                                        title="{{ trans('order::orders.treatment_status_title', ['status' => $treatmentBooking->treatmentStatusLabel()]) }}"
                                    >
                                        <span>{{ $treatmentBooking->treatmentStatusLabel() }}</span>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="order-show__crm-sub">
                        @if ($order->customer_email)
                            <a href="mailto:{{ $order->customer_email }}" class="order-show__crm-sub-item">{{ $order->customer_email }}</a>
                            <span class="order-show__crm-sep" aria-hidden="true">·</span>
                        @endif
                        @if ($order->customer_phone)
                            <a href="tel:{{ $order->customer_phone }}" class="order-show__crm-sub-item">{{ $order->customer_phone }}</a>
                            <span class="order-show__crm-sep" aria-hidden="true">·</span>
                        @endif
                        <span class="order-show__crm-sub-item">#{{ $order->id }}</span>
                        @if ($customerProfileUrl)
                            @hasAccess('admin.users.edit')
                                <a href="{{ $customerProfileUrl }}" class="order-show__crm-external" title="{{ trans('order::orders.customer_record') }}">
                                    <i class="fa fa-external-link" aria-hidden="true"></i>
                                </a>
                            @endHasAccess
                        @endif
                    </div>

                    <div class="order-show__crm-meta">
                        @if ($hasBirthday)
                            <span title="{{ trans('order::orders.customer_date_of_birth') }}">
                                {{ trans('order::orders.customer_birthday_meta', [
                                    'date' => $customerDob->format('d M Y'),
                                    'age' => $customerAge,
                                ]) }}
                            </span>
                            <span class="order-show__crm-sep" aria-hidden="true">·</span>
                        @elseif ($order->customer)
                            <span class="order-show__crm-meta--muted" title="{{ trans('order::orders.customer_date_of_birth') }}">
                                {{ trans('order::orders.customer_birthday_missing') }}
                            </span>
                            <span class="order-show__crm-sep" aria-hidden="true">·</span>
                        @endif
                        <span title="{{ trans('order::orders.deal_value') }}">
                            {{ trans('order::orders.deal_value') }}: <strong>{{ $order->total->format() }}</strong>
                        </span>
                    </div>

                    @if ($hasCustomerIdentity)
                        <div class="order-show__crm-stats" role="list">
                            <span
                                class="order-show__crm-stat"
                                role="listitem"
                                title="{{ trans('order::orders.customer_segment') }}: {{ $order->customerRecencyBadgeLabel() }}"
                            >
                                <i class="fa fa-user" aria-hidden="true"></i>
                                <span>{{ $order->customerRecencyBadgeLabel() }}</span>
                            </span>
                            <span class="order-show__crm-sep" aria-hidden="true">·</span>
                            <span
                                class="order-show__crm-stat"
                                role="listitem"
                                title="{{ trans('order::orders.customer_purchases') }}: {{ trans('order::orders.customer_purchase_ordinal', ['number' => $purchaseOrdinal]) }}"
                            >
                                <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                                <span>{{ number_format($purchaseCount) }}</span>
                            </span>
                            @if ($customerVisitLabel)
                                <span class="order-show__crm-sep" aria-hidden="true">·</span>
                                <span
                                    class="order-show__crm-stat"
                                    role="listitem"
                                    title="{{ trans('order::orders.customer_visit') }}: {{ $customerVisitLabel }}"
                                >
                                    <i class="fa fa-map-marker" aria-hidden="true"></i>
                                    <span>{{ $customerVisitLabel }}</span>
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
