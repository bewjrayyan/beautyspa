@php
    $stampCards = $stampCards ?? [];
    $loyaltyWallet = $loyaltyWallet ?? null;
@endphp

<div class="order-show__section order-show__section--stamps">
    <h4 class="order-show__section-title">
        <span class="order-show__section-title-text">{{ trans('loyalty::orders.stamps.title') }}</span>
        @if ($stampCards !== [])
            <span class="order-show__section-count">{{ count($stampCards) }}</span>
        @endif
    </h4>

    <p class="order-show__hint order-show__hint--section">
        {{ trans('loyalty::orders.stamps.lead') }}
        @if ($loyaltyWallet)
            @hasAccess('admin.loyalty.members.show')
                <a href="{{ route('admin.loyalty.members.show', $loyaltyWallet) }}">
                    {{ trans('loyalty::orders.stamps.view_member') }}
                </a>
            @endHasAccess
        @endif
    </p>

    @if ($stampCards === [])
        <div class="order-show__card">
            <p class="order-show__hint">{{ trans('loyalty::orders.stamps.empty') }}</p>
        </div>
    @else
        <div class="loyalty-stamp-cards order-show-stamp-cards">
            @foreach ($stampCards as $card)
                <div class="order-show-stamp-card-wrap">
                    @include('loyalty::public.partials.stamp-card', [
                        'card' => $card,
                        'checkIconClass' => 'fa fa-check',
                        'clockIconClass' => 'fa fa-clock-o',
                        'giftIconClass' => 'fa fa-gift',
                    ])

                    <div class="order-show-stamp-card__admin-meta">
                        <span class="order-show-stamp-card__award">
                            {{ trans_choice('loyalty::orders.stamps.added_from_order', $card['stamps_added_this_order'] ?? 1, [
                                'count' => $card['stamps_added_this_order'] ?? 1,
                            ]) }}
                        </span>

                        <span @class([
                            'label',
                            'order-show-stamp-card__status',
                            'label-info' => ($card['admin_status'] ?? '') === 'in_progress',
                            'label-success' => ($card['admin_status'] ?? '') === 'pending_customer_redeem',
                            'label-warning' => ($card['admin_status'] ?? '') === 'valid',
                            'label-default' => in_array($card['admin_status'] ?? '', ['fulfilled', 'expired'], true),
                        ])>
                            {{ trans('loyalty::orders.stamps.status_' . ($card['admin_status'] ?? 'in_progress')) }}
                        </span>

                        @if (! empty($card['redemption_code']))
                            <code class="order-show-stamp-card__code">{{ $card['redemption_code'] }}</code>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
