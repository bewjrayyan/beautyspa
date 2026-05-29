@if ($canSendOrderWhatsApp ?? false)
    <div
        id="order-whatsapp-actions"
        class="order-show__whatsapp"
        data-order-id="{{ $order->id }}"
    >
        <span class="order-show__whatsapp-label">{{ trans('order::orders.action_group_whatsapp') }}</span>
        <div class="order-show__whatsapp-buttons" role="group" aria-label="{{ trans('order::orders.action_group_whatsapp') }}">
            <button
                type="button"
                class="btn order-show__whatsapp-btn js-order-whatsapp-send"
                data-whatsapp-type="invoice"
                data-send-url="{{ route('admin.orders.whatsapp.invoice', $order) }}"
                data-sending-label="{{ trans('order::whatsapp.sending') }}"
            >
                <i class="fa fa-whatsapp" aria-hidden="true"></i>
                <span class="order-show__whatsapp-btn-text">{{ trans('order::whatsapp.send_invoice') }}</span>
            </button>
            <button
                type="button"
                class="btn order-show__whatsapp-btn js-order-whatsapp-send"
                data-whatsapp-type="receipt"
                data-send-url="{{ route('admin.orders.whatsapp.receipt', $order) }}"
                data-sending-label="{{ trans('order::whatsapp.sending') }}"
            >
                <i class="fa fa-whatsapp" aria-hidden="true"></i>
                <span class="order-show__whatsapp-btn-text">{{ trans('order::whatsapp.send_receipt') }}</span>
            </button>
        </div>
    </div>
@endif
