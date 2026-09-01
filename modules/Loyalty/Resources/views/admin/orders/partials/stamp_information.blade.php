@php
    $stampCards = $stampCards ?? [];
    $loyaltyWallet = $loyaltyWallet ?? null;
    $variant = $variant ?? 'sidebar';
@endphp

@if ($variant === 'inline')
    <div class="order-show__items-footer-stamps">
        <h4 class="order-show__items-footer-stamps-title">{{ trans('loyalty::orders.stamps.title') }}</h4>
        <p class="order-show__items-footer-stamps-lead">{{ trans('loyalty::orders.stamps.lead') }}</p>

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
    </div>
@else
    <div class="order-show__card order-show__card--stamps">
        <h4 class="order-show__section-title">
            <span class="order-show__section-title-text">{{ trans('loyalty::orders.stamps.title') }}</span>
        </h4>

        <p class="order-show__hint order-show__hint--section">
            {{ trans('loyalty::orders.stamps.lead') }}
        </p>

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
    </div>
@endif
