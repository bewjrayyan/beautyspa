@php
    $stampCards = $orderRewards['stamp_cards'] ?? [];
    $pointsBalance = (int) ($orderRewards['points_balance'] ?? 0);
    $pointsWorthRm = (float) ($orderRewards['points_worth_rm'] ?? 0);
    $currency = currency_symbol(setting('default_currency'));
@endphp

<div class="order-complete-section order-complete-rewards" id="order-rewards">
    <h2 class="order-complete-section-title">
        <i class="las la-gift"></i>
        {{ trans('loyalty::order_rewards.title') }} 🎉
    </h2>

    @if ($stampCards !== [])
        <div class="order-complete-rewards__block">
            <h3 class="order-complete-rewards__subtitle">{{ trans('loyalty::order_rewards.stamp_cards') }}</h3>

            @include('loyalty::public.partials.stamp-cards', [
                'stampCards' => $stampCards,
                'showRedeemActions' => false,
                'wrapperClass' => 'order-complete-stamp-cards',
                'cardClass' => 'order-complete-stamp-card',
            ])
        </div>
    @endif

    @if ($pointsBalance > 0)
        <div class="order-complete-rewards__block">
            <h3 class="order-complete-rewards__subtitle">{{ trans('loyalty::order_rewards.loyalty_points') }}</h3>
            <p class="order-complete-rewards__points">
                <i class="las la-tag"></i>
                {{ trans('loyalty::order_rewards.points_balance', [
                    'points' => number_format($pointsBalance),
                    'worth' => $currency . number_format($pointsWorthRm, 2),
                ]) }}
            </p>
        </div>
    @endif
</div>
