@if (filled($order->note))
    @php
        $customerNote = $order->customerOrderNote();
        $exportNote = $order->exportOrderNoteLines();
    @endphp
    <div class="order-show__card order-show__card--customer-note">
        <div class="order-show__card-head">
            <h5>
                <i class="fa fa-comment-o" aria-hidden="true"></i>
                {{ $customerNote ? trans('order::orders.customer_order_note') : trans('order::orders.order_note') }}
            </h5>
        </div>

        @if ($customerNote)
            <p class="order-show__prewrap">{{ $customerNote }}</p>
        @endif

        @if ($exportNote)
            @if ($customerNote)
                <p class="order-show__note-box-label order-show__note-box-label--spaced">{{ trans('order::orders.order_note_export') }}</p>
            @endif
            <p class="order-show__prewrap {{ $customerNote ? 'order-show__hint' : '' }}">{{ $exportNote }}</p>
        @endif

        @if (! $customerNote && ! $exportNote)
            <p class="order-show__prewrap">{{ $order->note }}</p>
        @endif
    </div>
@endif
