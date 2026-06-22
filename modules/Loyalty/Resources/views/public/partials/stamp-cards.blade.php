@php
    $stampCards = $stampCards ?? [];
    $showRedeemActions = $showRedeemActions ?? false;
    $wrapperClass = $wrapperClass ?? 'loyalty-stamp-cards';
    $cardClass = $cardClass ?? 'loyalty-stamp-card';
    $checkIconClass = $checkIconClass ?? 'las la-check';
    $clockIconClass = $clockIconClass ?? 'las la-clock';
    $giftIconClass = $giftIconClass ?? 'las la-gift';
@endphp

@if ($stampCards !== [])
    <div class="{{ $wrapperClass }}">
        @foreach ($stampCards as $card)
            @include('loyalty::public.partials.stamp-card', compact(
                'card',
                'cardClass',
                'showRedeemActions',
                'checkIconClass',
                'clockIconClass',
                'giftIconClass'
            ))
        @endforeach
    </div>
@endif
