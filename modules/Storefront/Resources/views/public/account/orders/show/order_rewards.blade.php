@if (! empty($orderRewards))
    <section @class([
        'account-order-show__section',
        'account-order-rewards-wrap',
        $wrapperClass ?? null,
    ])>
        @include('loyalty::public.order_complete.rewards', [
            'orderRewards' => $orderRewards,
            'variant' => 'account-order',
        ])
    </section>
@endif
