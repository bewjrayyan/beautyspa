<div class="order-show__hero">
    <div class="order-show__hero-top">
        <div class="order-show__identity">
            <div class="order-show__identity-main">
                @include('order::admin.orders.partials.customer_avatar', ['order' => $order])

                <div class="order-show__identity-body">
                    <div class="order-show__identity-labels">
                        <span class="order-show__order-id">#{{ $order->id }}</span>
                        @if ($order->customer_id || filled($order->customer_email))
                            <span class="order-show__customer-badge order-show__customer-badge--{{ $order->isReturningCustomer() ? 'returning' : 'new' }}">
                                {{ $order->customerRecencyBadgeLabel() }}
                            </span>
                        @endif
                    </div>
                    <h2 class="order-show__customer-name">{{ $order->customer_full_name }}</h2>
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
                </div>
            </div>
        </div>

        <aside class="order-show__hero-panel" aria-label="{{ trans('order::orders.order_summary') }}">
            <div class="order-show__hero-stats">
                <div class="order-show__stat">
                    <span class="order-show__stat-label">{{ trans('order::orders.order_status') }}</span>
                    <span
                        class="order-show__chip"
                        id="order-status-badge"
                        data-status-type="order"
                        data-status="{{ $order->status }}"
                    >{{ $order->status() }}</span>
                </div>
                <div class="order-show__stat">
                    <span class="order-show__stat-label">{{ trans('order::orders.payment_status') }}</span>
                    <span
                        class="order-show__chip"
                        id="order-payment-status-badge"
                        data-status-type="payment"
                        data-status="{{ $order->payment_status }}"
                    >{{ $order->paymentStatusLabel() }}</span>
                </div>
                @if (!empty($treatmentBooking))
                    <div class="order-show__stat">
                        <span class="order-show__stat-label">{{ trans('order::orders.treatment_status') }}</span>
                        <span
                            class="order-show__chip"
                            id="order-treatment-status-badge"
                            data-status-type="treatment"
                            data-status="{{ $treatmentBooking->status }}"
                        >{{ $treatmentBooking->treatmentStatusLabel() }}</span>
                    </div>
                @endif
            </div>
            <div class="order-show__hero-total">
                <span class="order-show__hero-total-label">{{ trans('order::orders.total') }}</span>
                <strong class="order-show__hero-total-value">{{ $order->total->format() }}</strong>
            </div>
        </aside>
    </div>

    <div class="order-show__command-bar">
        <div class="order-show__status-strip">
            <div class="order-show__status-field order-show__status-field--order">
                <label for="order-status" title="{{ trans('order::orders.order_status_help') }}">{{ trans('order::orders.order_status') }}</label>
                <select id="order-status" class="form-control custom-select-black order-show__status-select" data-id="{{ $order->id }}">
                    @foreach (trans('order::statuses') as $name => $label)
                        <option value="{{ $name }}" {{ $order->status === $name ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="order-show__status-field order-show__status-field--payment">
                <label for="order-payment-status" title="{{ trans('order::orders.payment_status_help') }}">{{ trans('order::orders.payment_status') }}</label>
                <select id="order-payment-status" class="form-control custom-select-black order-show__status-select" data-id="{{ $order->id }}">
                    @foreach (trans('order::payment_statuses') as $name => $label)
                        <option value="{{ $name }}" {{ $order->payment_status === $name ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if (!empty($treatmentBooking))
                <div class="order-show__status-field order-show__status-field--treatment">
                    <label for="order-treatment-status" title="{{ trans('order::orders.treatment_status_help') }}">{{ trans('order::orders.treatment_status') }}</label>
                    <select
                        id="order-treatment-status"
                        class="form-control custom-select-black order-show__status-select"
                        data-id="{{ $order->id }}"
                    >
                        @foreach (\Modules\TreatmentReservation\Entities\TreatmentBooking::statuses() as $status)
                            <option value="{{ $status }}" {{ $treatmentBooking->status === $status ? 'selected' : '' }}>
                                {{ $treatmentBooking::STATUS_CANCELED === $status
                                    ? trans('treatmentreservation::admin.crm.status_canceled')
                                    : trans('treatmentreservation::admin.kanban.' . $status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="order-show__actions">
            @include('order::admin.orders.partials.order_whatsapp_actions', [
                'order' => $order,
                'canSendOrderWhatsApp' => $canSendOrderWhatsApp ?? false,
            ])

            <div class="order-show__control order-show__control--actions">
                <label id="order-actions-label" class="sr-only">{{ trans('order::orders.actions') }}</label>
                <div
                    id="order-actions"
                    class="btn-group order-show__actions-dropdown"
                    data-print-url="{{ route('admin.orders.print.show', $order) }}"
                    data-receipt-url="{{ route('admin.orders.receipt.show', $order) }}"
                    data-back-url="{{ route('admin.orders.index') }}"
                >
                    <button
                        type="button"
                        class="btn dropdown-toggle order-show__actions-toggle"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-labelledby="order-actions-label"
                    >
                        <span class="order-show__actions-toggle-icon" aria-hidden="true">
                            <i class="fa fa-bolt"></i>
                        </span>
                        <span class="order-show__actions-toggle-label">{{ trans('order::orders.select_action') }}</span>
                        <span class="order-show__actions-toggle-chevron" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right order-show__actions-menu">
                        <li class="dropdown-header">{{ trans('order::orders.action_group_documents') }}</li>
                        <li>
                            <a href="{{ route('admin.orders.print.show', $order) }}" class="js-order-action order-show__action-item order-show__action-item--print" data-action="print" target="_blank" rel="noopener noreferrer">
                                <span class="order-show__action-icon"><i class="fa fa-print" aria-hidden="true"></i></span>
                                <span class="order-show__action-text">
                                    <span class="order-show__action-title">{{ trans('order::orders.print') }}</span>
                                    <span class="order-show__action-desc">{{ trans('order::orders.action_print_desc') }}</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.receipt.show', $order) }}" class="js-order-action order-show__action-item order-show__action-item--receipt" data-action="receipt" target="_blank" rel="noopener noreferrer">
                                <span class="order-show__action-icon"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                                <span class="order-show__action-text">
                                    <span class="order-show__action-title">{{ trans('order::orders.receipt') }}</span>
                                    <span class="order-show__action-desc">{{ trans('order::orders.action_receipt_desc') }}</span>
                                </span>
                            </a>
                        </li>
                        <li class="dropdown-header">{{ trans('order::orders.action_group_other') }}</li>
                        <li>
                            <button type="button" class="js-order-action order-show__action-item order-show__action-item--email" data-action="email">
                                <span class="order-show__action-icon"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                                <span class="order-show__action-text">
                                    <span class="order-show__action-title">{{ trans('order::orders.send_email') }}</span>
                                    <span class="order-show__action-desc">{{ trans('order::orders.action_email_desc') }}</span>
                                </span>
                            </button>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.index') }}" class="js-order-action order-show__action-item order-show__action-item--back" data-action="back">
                                <span class="order-show__action-icon"><i class="fa fa-arrow-left" aria-hidden="true"></i></span>
                                <span class="order-show__action-text">
                                    <span class="order-show__action-title">{{ trans('order::orders.back_to_orders') }}</span>
                                    <span class="order-show__action-desc">{{ trans('order::orders.action_back_desc') }}</span>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <form
                id="order-email-form"
                method="POST"
                action="{{ route('admin.orders.email.store', $order) }}"
                class="order-show__email-form"
                hidden
            >
                @csrf
            </form>
        </div>
    </div>
</div>
