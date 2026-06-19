@if (! empty($orderRewards))
    <section class="account-order-show__section account-order-rewards-wrap">
        @include('loyalty::public.order_complete.rewards', ['orderRewards' => $orderRewards])
    </section>
@endif
