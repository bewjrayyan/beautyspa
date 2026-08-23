@php
    $stampCards = $orderRewards['stamp_cards'] ?? [];
    $pointsBalance = (int) ($orderRewards['points_balance'] ?? 0);
    $pointsWorthRm = (float) ($orderRewards['points_worth_rm'] ?? 0);
    $formattedPointsWorth = currency_symbol_fallback('MYR').' '.number_format($pointsWorthRm, 2);
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

            @if (($variant ?? null) === 'account-order')
                <div class="order-complete-rewards__points-card">
                    <span class="order-complete-rewards__points-icon" aria-hidden="true">
                        <i class="las la-coins"></i>
                    </span>

                    <span class="order-complete-rewards__points-balance">
                        <span class="order-complete-rewards__metric-label">{{ trans('loyalty::account.points_balance') }}</span>
                        <strong>{{ number_format($pointsBalance) }}</strong>
                        <span class="order-complete-rewards__points-unit">{{ trans('loyalty::order_rewards.loyalty_points') }}</span>
                    </span>

                    <span class="order-complete-rewards__points-worth">
                        <span class="order-complete-rewards__metric-label">{{ trans('loyalty::order_rewards.equivalent_value') }}</span>
                        <strong>{{ $formattedPointsWorth }}</strong>
                    </span>

                    <span class="sr-only">
                        {{ trans('loyalty::order_rewards.points_balance', [
                            'points' => number_format($pointsBalance),
                            'worth' => $formattedPointsWorth,
                        ]) }}
                    </span>
                </div>
            @else
                <p class="order-complete-rewards__points">
                    <i class="las la-tag"></i>
                    {{ trans('loyalty::order_rewards.points_balance', [
                        'points' => number_format($pointsBalance),
                        'worth' => $formattedPointsWorth,
                    ]) }}
                </p>
            @endif
        </div>
    @endif
</div>
