@php
    $customerProfileUrl = $order->customer
        ? route('admin.users.edit', $order->customer)
        : null;
    $orderBookings = isset($treatmentBookings) && $treatmentBookings instanceof \Illuminate\Support\Collection
        ? $treatmentBookings
        : collect(! empty($treatmentBooking) ? [$treatmentBooking] : []);
    $primaryBooking = $orderBookings->first();
    $orderWorkspaceUrl = request()->url();
@endphp

<div class="order-show__hero">
    <div class="order-show__workspace-bar">
        <div class="order-show__workspace-heading">
            <span class="order-show__workspace-icon" aria-hidden="true"><i class="fa fa-briefcase"></i></span>
            <div>
                <span class="order-show__workspace-eyebrow">{{ trans('order::orders.order_workspace') }}</span>
                <span class="order-show__workspace-description">{{ trans('order::orders.order_workspace_description') }}</span>
            </div>
        </div>

        <div class="order-show__workspace-actions">
            <span class="order-show__updated-at">
                <i class="fa fa-clock-o" aria-hidden="true"></i>
                {{ trans('order::orders.last_updated', ['time' => $order->updated_at->format('d M Y, H:i')]) }}
            </span>
            <a href="{{ route('admin.orders.index') }}" class="order-show__back-link">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                {{ trans('order::orders.back_to_orders') }}
            </a>
        </div>
    </div>

    <div class="order-show__hero-top">
        <div class="order-show__identity">
            <div class="order-show__identity-main">
                @include('order::admin.orders.partials.customer_avatar', [
                    'order' => $order,
                    'customerProfileUrl' => $customerProfileUrl,
                ])

                <div class="order-show__identity-body">
                    <div class="order-show__identity-labels">
                        <span class="order-show__order-id">#{{ $order->id }}</span>
                        @if ($order->customer_id || filled($order->customer_email))
                            <span class="order-show__customer-badge order-show__customer-badge--{{ $order->isReturningCustomer() ? 'returning' : 'new' }}">
                                {{ $order->customerRecencyBadgeLabel() }}
                            </span>
                        @endif
                    </div>
                    <h2 class="order-show__customer-name">
                        @if ($customerProfileUrl)
                            @hasAccess('admin.users.edit')
                                <a href="{{ $customerProfileUrl }}" class="order-show__customer-name-link">
                                    <span>{{ $order->customer_full_name }}</span>
                                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                                </a>
                            @else
                                {{ $order->customer_full_name }}
                            @endHasAccess
                        @else
                            {{ $order->customer_full_name }}
                        @endif
                    </h2>
                    <div class="order-show__meta">
                        <span class="order-show__meta-chip">
                            <i class="fa fa-calendar-o" aria-hidden="true"></i>
                            <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                        </span>
                        @if ($order->customer_email)
                            <a href="mailto:{{ $order->customer_email }}" class="order-show__meta-chip order-show__meta-chip--link">
                                <i class="fa fa-envelope-o" aria-hidden="true"></i>
                                <span>{{ $order->customer_email }}</span>
                            </a>
                        @endif
                        @if ($order->customer_phone)
                            <a href="tel:{{ $order->customer_phone }}" class="order-show__meta-chip order-show__meta-chip--link">
                                <i class="fa fa-phone" aria-hidden="true"></i>
                                <span>{{ $order->customer_phone }}</span>
                            </a>
                        @endif
                        @if ($order->customer?->date_of_birth)
                            <span class="order-show__meta-chip">
                                <i class="fa fa-birthday-cake" aria-hidden="true"></i>
                                <span>
                                    {{ $order->customer->date_of_birth->format('d M Y') }}
                                    <span class="order-show__meta-age">({{ trans('order::orders.customer_age', ['age' => $order->customer->age()]) }})</span>
                                </span>
                            </span>
                        @endif
                    </div>
                    <div class="order-show__hero-stats" role="list">
                        <div class="order-show__stat" role="listitem">
                            <span class="order-show__stat-label">{{ trans('order::orders.order_status_badge') }}</span>
                            <span
                                class="order-show__chip"
                                id="order-status-badge"
                                data-status-type="order"
                                data-status="{{ $order->status }}"
                            >{{ $order->status() }}</span>
                        </div>
                        <div class="order-show__stat" role="listitem">
                            <span class="order-show__stat-label">{{ trans('order::orders.payment_status_badge') }}</span>
                            <span
                                class="order-show__chip"
                                id="order-payment-status-badge"
                                data-status-type="payment"
                                data-status="{{ $order->payment_status }}"
                            >{{ $order->paymentStatusLabel() }}</span>
                        </div>
                        @if (!empty($treatmentBooking))
                            <div class="order-show__stat" role="listitem">
                                <span class="order-show__stat-label">{{ trans('order::orders.treatment_status_badge') }}</span>
                                <span
                                    class="order-show__chip"
                                    id="order-treatment-status-badge"
                                    data-status-type="treatment"
                                    data-status="{{ $treatmentBooking->status }}"
                                >{{ $treatmentBooking->treatmentStatusLabel() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <aside class="order-show__hero-panel" aria-label="{{ trans('order::orders.order_summary') }}">
            <div class="order-show__hero-total">
                <span class="order-show__hero-total-label">{{ trans('order::orders.total') }}</span>
                <strong class="order-show__hero-total-value">{{ $order->total->format() }}</strong>
            </div>
        </aside>
    </div>

    <div class="order-show__snapshot" aria-label="{{ trans('order::orders.operational_snapshot') }}">
        <div class="order-show__snapshot-item">
            <span class="order-show__snapshot-icon order-show__snapshot-icon--commerce" aria-hidden="true"><i class="fa fa-shopping-bag"></i></span>
            <span class="order-show__snapshot-copy">
                <small>{{ trans('order::orders.items_ordered') }}</small>
                <strong>{{ trans_choice('order::orders.items_count', $order->products->count(), ['count' => $order->products->count()]) }}</strong>
            </span>
        </div>
        <div class="order-show__snapshot-item">
            <span class="order-show__snapshot-icon order-show__snapshot-icon--payment" aria-hidden="true"><i class="fa fa-credit-card"></i></span>
            <span class="order-show__snapshot-copy">
                <small>{{ trans('order::orders.payment_method') }}</small>
                <strong>{{ $order->payment_method ?: '—' }}</strong>
            </span>
        </div>
        <div class="order-show__snapshot-item">
            <span class="order-show__snapshot-icon order-show__snapshot-icon--appointment" aria-hidden="true"><i class="fa fa-calendar-check-o"></i></span>
            <span class="order-show__snapshot-copy">
                <small>{{ trans('order::orders.appointment_date') }}</small>
                <strong>{{ $primaryBooking?->appointment_date?->format('d M Y') ?? $order->appointment_date?->format('d M Y') ?? '—' }}</strong>
            </span>
        </div>
        <div class="order-show__snapshot-item">
            <span class="order-show__snapshot-icon order-show__snapshot-icon--branch" aria-hidden="true"><i class="fa fa-building-o"></i></span>
            <span class="order-show__snapshot-copy">
                <small>{{ trans('order::orders.spa_branch') }}</small>
                <strong>{{ $primaryBooking?->spaBranchLabel() ?? $order->spaBranch?->name ?? '—' }}</strong>
            </span>
        </div>
    </div>

    <nav class="order-show__workspace-nav" aria-label="{{ trans('order::orders.workspace_navigation') }}">
        <a href="{{ $orderWorkspaceUrl }}#order-items" data-order-section="order-items" aria-controls="order-items"><i class="fa fa-shopping-bag" aria-hidden="true"></i>{{ trans('order::orders.items_ordered') }}</a>
        <a href="{{ $orderWorkspaceUrl }}#order-overview" data-order-section="order-overview" aria-controls="order-overview"><i class="fa fa-file-text-o" aria-hidden="true"></i>{{ trans('order::orders.order_information') }}</a>
        @if ($order->hasAppointmentDetails() || $orderBookings->isNotEmpty())
            <a href="{{ $orderWorkspaceUrl }}#order-fulfillment" data-order-section="order-fulfillment" aria-controls="order-fulfillment"><i class="fa fa-calendar-check-o" aria-hidden="true"></i>{{ trans('order::orders.appointment_information') }}</a>
        @endif
        <a href="{{ $orderWorkspaceUrl }}#order-customer" data-order-section="order-customer" aria-controls="order-customer"><i class="fa fa-address-card-o" aria-hidden="true"></i>{{ trans('order::orders.address_information') }}</a>
        @if (! empty($treatmentBooking?->activities) && $treatmentBooking->activities->isNotEmpty())
            <a href="{{ $orderWorkspaceUrl }}#order-activity" data-order-section="order-activity" aria-controls="order-activity"><i class="fa fa-history" aria-hidden="true"></i>{{ trans('order::orders.activity') }}</a>
        @endif
    </nav>
</div>
