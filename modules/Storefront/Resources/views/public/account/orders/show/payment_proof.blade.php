@php
    $proof = $order->paymentProof;
    $isBankTransfer = $order->getRawOriginal('payment_method') === 'bank_transfer';
    $isImage = $proof && (
        str_starts_with((string) $proof->mime, 'image/')
        || in_array(strtolower((string) $proof->extension), ['jpg', 'jpeg', 'png', 'webp'], true)
    );
@endphp

@if ($isBankTransfer || $proof)
    <div class="account-order-payment-proof">
        <span class="account-order-payment-proof__label">
            {{ trans('storefront::account.view_order.payment_proof') }}
        </span>

        @if ($proof)
            <div class="account-order-payment-proof__body">
                @if ($isImage)
                    <a
                        href="{{ $proof->path }}"
                        class="account-order-payment-proof__preview"
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
                        class="account-order-payment-proof__file"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <i class="las la-file-pdf" aria-hidden="true"></i>
                        <span>{{ $proof->filename }}</span>
                    </a>
                @endif

                <a
                    href="{{ $proof->path }}"
                    class="account-order-payment-proof__link"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="las la-external-link-alt" aria-hidden="true"></i>
                    {{ trans('storefront::account.view_order.view_payment_proof') }}
                </a>
            </div>
        @else
            <p class="account-order-payment-proof__missing">
                {{ trans('storefront::account.view_order.payment_proof_missing') }}
            </p>
        @endif
    </div>
@endif
