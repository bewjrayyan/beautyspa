@php
    $proof = $order->paymentProof;
    $isImage = $proof && (
        str_starts_with((string) $proof->mime, 'image/')
        || in_array(strtolower((string) $proof->extension), ['jpg', 'jpeg', 'png', 'webp'], true)
    );
@endphp

@if ($proof)
    <div class="order-show__hint order-show__hint--payment-proof">
        @if ($isImage)
            <a
                href="{{ $proof->path }}"
                class="order-show__payment-proof-preview order-show__payment-proof-preview--inline"
                target="_blank"
                rel="noopener noreferrer"
            >
                <img
                    src="{{ $proof->path }}"
                    alt="{{ $proof->filename }}"
                    loading="lazy"
                >
            </a>
        @else
            <a
                href="{{ $proof->path }}"
                class="order-show__payment-proof-file order-show__payment-proof-file--inline"
                target="_blank"
                rel="noopener noreferrer"
            >
                <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                <span>{{ $proof->filename }}</span>
            </a>
        @endif

        <a
            href="{{ $proof->path }}"
            class="order-show__payment-proof-link"
            target="_blank"
            rel="noopener noreferrer"
        >
            <i class="fa fa-external-link" aria-hidden="true"></i>
            {{ trans('order::orders.view_payment_proof') }}
        </a>
    </div>
@else
    <div class="order-show__hint order-show__hint--payment-proof-missing">
        {{ trans('order::orders.payment_proof_missing') }}
    </div>
@endif
