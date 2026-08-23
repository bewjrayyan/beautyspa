@php
    $rewardData = $orderRewardData ?? null;
    $wallet = $rewardData['wallet'] ?? null;
    $transactions = $rewardData['transactions'] ?? collect();
    $stampCards = $rewardData['stamp_cards'] ?? [];
@endphp

@if ($rewardData && $wallet)
    <section id="order-rewards" class="order-show__section order-rewards" aria-labelledby="order-rewards-title">
        <div class="order-rewards__head">
            <div>
                <span class="order-show__card-eyebrow">{{ trans('loyalty::orders.rewards.eyebrow') }}</span>
                <h3 id="order-rewards-title">
                    <i class="fa fa-gift" aria-hidden="true"></i>
                    {{ trans('loyalty::orders.rewards.title') }}
                </h3>
                <p>{{ trans('loyalty::orders.rewards.description') }}</p>
            </div>
            @hasAccess('admin.loyalty.members.show')
                <a href="{{ route('admin.loyalty.members.show', $wallet) }}" class="order-rewards__member-link">
                    {{ trans('loyalty::orders.rewards.view_member') }}
                    <i class="fa fa-arrow-right" aria-hidden="true"></i>
                </a>
            @endHasAccess
        </div>

        <div class="order-rewards__summary" role="list">
            <article class="order-rewards__metric order-rewards__metric--earned" role="listitem">
                <span class="order-rewards__metric-icon" aria-hidden="true"><i class="fa fa-plus"></i></span>
                <div>
                    <span>{{ trans('loyalty::orders.rewards.points_earned') }}</span>
                    <strong>+{{ number_format($rewardData['points_earned']) }} {{ trans('order::orders.loyalty_pts') }}</strong>
                    <small>
                        {{ $rewardData['ledger_points_earned'] > 0
                            ? trans('loyalty::orders.rewards.ledger_verified')
                            : trans('loyalty::orders.rewards.order_record') }}
                    </small>
                </div>
            </article>

            <article class="order-rewards__metric order-rewards__metric--redeemed" role="listitem">
                <span class="order-rewards__metric-icon" aria-hidden="true"><i class="fa fa-ticket"></i></span>
                <div>
                    <span>{{ trans('loyalty::orders.rewards.points_redeemed') }}</span>
                    <strong>&minus;{{ number_format($rewardData['points_redeemed']) }} {{ trans('order::orders.loyalty_pts') }}</strong>
                    <small>{{ trans('loyalty::orders.rewards.savings_value', [
                        'value' => \Modules\Support\Money::inDefaultCurrency($rewardData['redemption_value_rm'])->format(),
                    ]) }}</small>
                </div>
            </article>

            <article class="order-rewards__metric order-rewards__metric--balance" role="listitem">
                <span class="order-rewards__metric-icon" aria-hidden="true"><i class="fa fa-star"></i></span>
                <div>
                    <span>{{ trans('loyalty::orders.rewards.current_balance') }}</span>
                    <strong>{{ number_format($wallet->balance) }} {{ trans('order::orders.loyalty_pts') }}</strong>
                    <small>
                        {{ $wallet->tier?->name ?? trans('loyalty::orders.rewards.no_tier') }}
                        · {{ \Modules\Support\Money::inDefaultCurrency($rewardData['wallet_value_rm'])->format() }}
                    </small>
                </div>
            </article>

            <article class="order-rewards__metric order-rewards__metric--stamp" role="listitem">
                <span class="order-rewards__metric-icon" aria-hidden="true"><i class="fa fa-check-circle"></i></span>
                <div>
                    <span>{{ trans('loyalty::orders.rewards.visit_stamps') }}</span>
                    <strong>+{{ number_format($rewardData['stamps_added']) }}</strong>
                    <small>{{ trans_choice('loyalty::orders.rewards.programs_affected', count($stampCards), ['count' => count($stampCards)]) }}</small>
                </div>
            </article>
        </div>

        @if ($rewardData['earned_record_mismatch'] || $rewardData['redeemed_ledger_missing'])
            <div class="order-rewards__audit-alert" role="status">
                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                <div>
                    <strong>{{ trans('loyalty::orders.rewards.audit_attention') }}</strong>
                    @if ($rewardData['earned_record_mismatch'])
                        <p>{{ trans('loyalty::orders.rewards.earned_mismatch', [
                            'ledger' => number_format($rewardData['ledger_points_earned']),
                            'order' => number_format($rewardData['recorded_points_earned']),
                        ]) }}</p>
                    @endif
                    @if ($rewardData['redeemed_ledger_missing'])
                        <p>{{ trans('loyalty::orders.rewards.redemption_missing', [
                            'points' => number_format($rewardData['recorded_points_redeemed']),
                        ]) }}</p>
                    @endif
                </div>
            </div>
        @endif

        <div class="order-rewards__details-grid">
            <article class="order-rewards__panel">
                <div class="order-rewards__panel-head">
                    <span class="order-rewards__panel-icon" aria-hidden="true"><i class="fa fa-calculator"></i></span>
                    <div>
                        <h4>{{ trans('loyalty::orders.rewards.calculation_title') }}</h4>
                        <p>{{ trans('loyalty::orders.rewards.calculation_description') }}</p>
                    </div>
                </div>
                <dl class="order-rewards__facts">
                    <div><dt>{{ trans('loyalty::orders.rewards.eligible_spend') }}</dt><dd>{{ \Modules\Support\Money::inDefaultCurrency($rewardData['eligible_amount_rm'])->format() }}</dd></div>
                    <div><dt>{{ trans('loyalty::orders.rewards.base_rate') }}</dt><dd>{{ rtrim(rtrim(number_format($rewardData['earn_rate_per_rm'], 4), '0'), '.') }} {{ trans('loyalty::orders.rewards.points_per_rm') }}</dd></div>
                    <div><dt>{{ trans('loyalty::orders.rewards.tier_multiplier') }}</dt><dd>{{ rtrim(rtrim(number_format($rewardData['tier_multiplier'], 2), '0'), '.') }}&times;</dd></div>
                    <div><dt>{{ trans('loyalty::orders.rewards.calculated_award') }}</dt><dd><strong>{{ number_format($rewardData['calculated_points']) }} {{ trans('order::orders.loyalty_pts') }}</strong></dd></div>
                    <div><dt>{{ trans('loyalty::orders.rewards.payment_gateway') }}</dt><dd>{{ $rewardData['payment_method'] ?: '—' }}</dd></div>
                    <div>
                        <dt>{{ trans('loyalty::orders.rewards.gateway_eligibility') }}</dt>
                        <dd>
                            <span class="order-rewards__policy-badge">
                                <i class="fa fa-unlock" aria-hidden="true"></i>
                                {{ trans('loyalty::orders.rewards.all_gateways') }}
                            </span>
                            <small>{{ trans('loyalty::orders.rewards.no_gateway_whitelist') }}</small>
                        </dd>
                    </div>
                    <div><dt>{{ trans('loyalty::orders.rewards.coupon_eligibility') }}</dt><dd>{{ $rewardData['allow_with_coupon'] ? trans('loyalty::orders.rewards.allowed') : trans('loyalty::orders.rewards.not_allowed') }}</dd></div>
                </dl>
            </article>

            <article class="order-rewards__panel">
                <div class="order-rewards__panel-head">
                    <span class="order-rewards__panel-icon" aria-hidden="true"><i class="fa fa-history"></i></span>
                    <div>
                        <h4>{{ trans('loyalty::orders.rewards.ledger_title') }}</h4>
                        <p>{{ trans('loyalty::orders.rewards.ledger_description') }}</p>
                    </div>
                </div>

                @forelse ($transactions as $transaction)
                    <div class="order-rewards__transaction">
                        <span @class([
                            'order-rewards__transaction-points',
                            'is-credit' => $transaction->points > 0,
                            'is-debit' => $transaction->points < 0,
                        ])>
                            {{ $transaction->points > 0 ? '+' : '' }}{{ number_format($transaction->points) }} {{ trans('order::orders.loyalty_pts') }}
                        </span>
                        <div>
                            <strong>{{ $transaction->description ?: trans('loyalty::orders.rewards.transaction') }}</strong>
                            <span>{{ $transaction->created_at?->format('d M Y, H:i') }} · {{ trans('loyalty::orders.rewards.balance_after', ['balance' => number_format($transaction->balance_after)]) }}</span>
                            @if ($transaction->expires_at)
                                <span>{{ trans('loyalty::orders.rewards.expires_at', ['date' => $transaction->expires_at->format('d M Y')]) }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="order-rewards__empty-ledger">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        {{ trans('loyalty::orders.rewards.no_ledger_transactions') }}
                    </div>
                @endforelse
            </article>
        </div>

    </section>
@endif
