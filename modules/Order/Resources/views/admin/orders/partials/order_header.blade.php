<div class="order-show__header">
    <div class="order-show__header-row">
        <div class="order-show__identity">
            <div class="order-show__identity-main">
                @include('order::admin.orders.partials.customer_avatar', ['order' => $order])

                <div class="order-show__identity-body">
                    <span class="order-show__order-id">#{{ $order->id }}</span>
                    <h2 class="order-show__customer-name">
                        <span class="order-show__customer-name-text">{{ $order->customer_full_name }}</span>
                        @if ($order->customer_id || filled($order->customer_email))
                            <span class="badge order-show__customer-badge order-show__customer-badge--{{ $order->isReturningCustomer() ? 'returning' : 'new' }}">
                                {{ $order->customerRecencyBadgeLabel() }}
                            </span>
                        @endif
                    </h2>
                    <p class="order-show__meta">
                        <i class="fa fa-calendar-o" aria-hidden="true"></i>
                        {{ $order->created_at->format('d M Y, H:i') }}
                        @if ($order->customer_email)
                            <span class="order-show__meta-sep">·</span>
                            <a href="mailto:{{ $order->customer_email }}" class="order-show__meta-link">
                                <i class="fa fa-envelope-o" aria-hidden="true"></i>
                                <span>{{ $order->customer_email }}</span>
                            </a>
                        @endif
                        @if ($order->customer_phone)
                            <span class="order-show__meta-sep">·</span>
                            <a href="tel:{{ $order->customer_phone }}" class="order-show__meta-link">
                                <i class="fa fa-phone" aria-hidden="true"></i>
                                <span>{{ $order->customer_phone }}</span>
                            </a>
                        @endif
                        @if ($order->customer?->date_of_birth)
                            <span class="order-show__meta-sep">·</span>
                            <span class="order-show__meta-static">
                                <i class="fa fa-birthday-cake" aria-hidden="true"></i>
                                <span>
                                    {{ $order->customer->date_of_birth->format('d M Y') }}
                                    <span class="order-show__meta-age">({{ trans('order::orders.customer_age', ['age' => $order->customer->age()]) }})</span>
                                </span>
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="order-show__summary">
            <span class="badge order-show__status-badge" id="order-status-badge">{{ $order->status() }}</span>
            <span class="badge order-show__status-badge" id="order-payment-status-badge">{{ $order->paymentStatusLabel() }}</span>
            @if (!empty($treatmentBooking))
                <span class="badge order-show__status-badge order-show__status-badge--treatment" id="order-treatment-status-badge">
                    {{ $treatmentBooking->treatmentStatusLabel() }}
                </span>
            @endif
            <span class="order-show__total">{{ $order->total->format() }}</span>
        </div>
    </div>

    <div class="order-show__toolbar">
        <div class="order-show__status-controls">
            <div class="order-show__control">
                <label for="order-status">{{ trans('order::orders.order_status') }}</label>
                <select id="order-status" class="form-control custom-select-black" data-id="{{ $order->id }}">
                    @foreach (trans('order::statuses') as $name => $label)
                        <option value="{{ $name }}" {{ $order->status === $name ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="order-show__control-hint">{{ trans('order::orders.order_status_help') }}</p>
            </div>
            <div class="order-show__control">
                <label for="order-payment-status">{{ trans('order::orders.payment_status') }}</label>
                <select id="order-payment-status" class="form-control custom-select-black" data-id="{{ $order->id }}">
                    @foreach (trans('order::payment_statuses') as $name => $label)
                        <option value="{{ $name }}" {{ $order->payment_status === $name ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="order-show__control-hint">{{ trans('order::orders.payment_status_help') }}</p>
            </div>
            @if (!empty($treatmentBooking))
                <div class="order-show__control">
                    <label for="order-treatment-status">{{ trans('order::orders.treatment_status') }}</label>
                    <select
                        id="order-treatment-status"
                        class="form-control custom-select-black"
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
                    <p class="order-show__control-hint">{{ trans('order::orders.treatment_status_help') }}</p>
                </div>
            @endif
        </div>

        <div class="order-show__actions">
            @include('order::admin.orders.partials.order_whatsapp_actions', [
                'order' => $order,
                'canSendOrderWhatsApp' => $canSendOrderWhatsApp ?? false,
            ])

            <div class="order-show__control order-show__control--actions">
                <label id="order-actions-label">{{ trans('order::orders.actions') }}</label>
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
