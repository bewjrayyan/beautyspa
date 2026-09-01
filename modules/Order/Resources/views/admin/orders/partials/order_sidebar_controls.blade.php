@php
    $hasTreatmentStatus = ! empty($treatmentBooking);
    $hasBankTransferReference = $order->getRawOriginal('payment_method') === 'bank_transfer';
@endphp

<div class="order-show__sidebar-actions">
    <section class="order-show__sidebar-block order-show__sidebar-block--actions" aria-labelledby="order-actions-label">
        <h6 id="order-actions-label" class="sr-only">{{ trans('order::orders.actions') }}</h6>
        <div class="order-show__actions">
            <div class="order-show__control order-show__control--actions">
                <div
                    id="order-actions"
                    class="btn-group order-show__actions-dropdown"
                    data-print-url="{{ route('admin.orders.print.show', $order) }}"
                    data-receipt-url="{{ route('admin.orders.receipt.show', $order) }}"
                    data-back-url="{{ route('admin.orders.index') }}"
                    data-order-id="{{ $order->id }}"
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
                        <span class="order-show__actions-toggle-label">{{ trans('order::orders.choose_action') }}</span>
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

                        @if ($canSendOrderWhatsApp ?? false)
                            <li class="dropdown-header">{{ trans('order::orders.action_group_whatsapp') }}</li>
                            <li>
                                <button
                                    type="button"
                                    class="js-order-whatsapp-send order-show__action-item order-show__action-item--whatsapp"
                                    data-whatsapp-type="invoice"
                                    data-send-url="{{ route('admin.orders.whatsapp.invoice', $order) }}"
                                    data-sending-label="{{ trans('order::whatsapp.sending') }}"
                                >
                                    <span class="order-show__action-icon"><i class="fa fa-whatsapp" aria-hidden="true"></i></span>
                                    <span class="order-show__action-text">
                                        <span class="order-show__action-title">{{ trans('order::whatsapp.send_invoice') }}</span>
                                        <span class="order-show__action-desc">{{ trans('order::orders.action_whatsapp_invoice_desc') }}</span>
                                    </span>
                                </button>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    class="js-order-whatsapp-send order-show__action-item order-show__action-item--whatsapp"
                                    data-whatsapp-type="receipt"
                                    data-send-url="{{ route('admin.orders.whatsapp.receipt', $order) }}"
                                    data-sending-label="{{ trans('order::whatsapp.sending') }}"
                                >
                                    <span class="order-show__action-icon"><i class="fa fa-whatsapp" aria-hidden="true"></i></span>
                                    <span class="order-show__action-text">
                                        <span class="order-show__action-title">{{ trans('order::whatsapp.send_receipt') }}</span>
                                        <span class="order-show__action-desc">{{ trans('order::orders.action_whatsapp_receipt_desc') }}</span>
                                    </span>
                                </button>
                            </li>
                        @endif

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
    </section>

    @if ($hasTreatmentStatus)
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

<div class="order-show__sidebar-controls">
    <div class="order-show__sidebar-controls-head">
        <h5 class="order-show__sidebar-controls-title">
            <i class="fa fa-sliders" aria-hidden="true"></i>
            {{ trans('order::orders.sidebar_controls_title') }}
        </h5>
        <p>{{ trans('order::orders.sidebar_controls_description') }}</p>
    </div>

    <section class="order-show__sidebar-block order-show__sidebar-block--status" aria-labelledby="order-sidebar-status-label">
        <h6 id="order-sidebar-status-label" class="order-show__sidebar-block-title">
            {{ trans('order::orders.sidebar_status_title') }}
        </h6>
        <div class="order-show__status-strip">
            <div class="order-show__status-field order-show__status-field--order">
                <label for="order-status" title="{{ trans('order::orders.order_status_help') }}">{{ trans('order::orders.order_status') }}</label>
                <select id="order-status" class="form-control custom-select-black order-show__status-select" data-id="{{ $order->id }}">
                    @foreach (\Modules\Order\Entities\Order::statuses() as $name)
                        <option value="{{ $name }}" {{ $order->status === $name ? 'selected' : '' }}>{{ trans('order::statuses.' . $name) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="order-show__status-field order-show__status-field--payment">
                <label for="order-payment-status" title="{{ trans('order::orders.payment_status_help') }}">{{ trans('order::orders.payment_status') }}</label>
                <select id="order-payment-status" class="form-control custom-select-black order-show__status-select" data-id="{{ $order->id }}">
                    @foreach (\Modules\Order\Entities\Order::paymentStatuses() as $name)
                        <option value="{{ $name }}" {{ $order->payment_status === $name ? 'selected' : '' }}>{{ trans('order::payment_statuses.' . $name) }}</option>
                    @endforeach
                </select>
            </div>

            @if ($hasBankTransferReference)
                <div
                    class="order-show__payment-reference"
                    id="order-payment-reference"
                    data-payment-method="bank_transfer"
                    data-requires-reference-for='@json(\Modules\Order\Services\OrderPaymentReferenceService::statusesRequiringReference())'
                    data-required-message="{{ e(trans('order::messages.payment_reference_required')) }}"
                >
                    <p class="order-show__payment-reference-title">
                        <i class="fa fa-university" aria-hidden="true"></i>
                        {{ trans('order::orders.payment_reference_title') }}
                    </p>
                    <p class="order-show__payment-reference-help">{{ trans('order::orders.payment_reference_help') }}</p>

                    <div class="order-show__payment-reference-proof">
                        <p class="order-show__payment-reference-proof-label">{{ trans('order::orders.payment_proof') }}</p>
                        @include('order::admin.orders.partials.order_payment_proof_inline')
                    </div>

                    <div class="form-group">
                        <label for="order-payment-transaction-id">{{ trans('order::orders.payment_reference_id') }}</label>
                        <input
                            type="text"
                            id="order-payment-transaction-id"
                            class="form-control"
                            maxlength="191"
                            value="{{ e($order->transaction?->transaction_id) }}"
                            placeholder="{{ trans('order::orders.payment_reference_id_placeholder') }}"
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-group">
                        <label for="order-payment-admin-note">{{ trans('order::orders.payment_reference_note') }}</label>
                        <textarea
                            id="order-payment-admin-note"
                            class="form-control"
                            rows="2"
                            maxlength="2000"
                            placeholder="{{ trans('order::orders.payment_reference_note_placeholder') }}"
                        >{{ e($order->transaction?->admin_note) }}</textarea>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" id="order-payment-reference-save">
                        <i class="fa fa-save" aria-hidden="true"></i>
                        {{ trans('order::orders.payment_reference_save') }}
                    </button>
                </div>
            @endif
        </div>
    </section>
</div>
