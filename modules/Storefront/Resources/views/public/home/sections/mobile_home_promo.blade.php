<section class="mobile-home-promo-wrap d-lg-none" aria-label="{{ trans('storefront::storefront.mobile_home_promo.section_label') }}">
    <div class="container">
        @php
            $tag = filled($mobileHomePromo['call_to_action_url'] ?? null) ? 'a' : 'div';
            $linkAttrs = $tag === 'a'
                ? sprintf(
                    ' href="%s" target="%s"%s',
                    e($mobileHomePromo['call_to_action_url']),
                    ($mobileHomePromo['open_in_new_window'] ?? false) ? '_blank' : '_self',
                    ($mobileHomePromo['open_in_new_window'] ?? false) ? ' rel="noopener noreferrer"' : ''
                )
                : '';
        @endphp

        <{{ $tag }} class="mobile-home-promo"{!! $linkAttrs !!}>
            @if (($mobileHomePromo['type'] ?? 'image') === 'video')
                <video
                    class="mobile-home-promo__video"
                    src="{{ $mobileHomePromo['url'] }}"
                    @if (! empty($mobileHomePromo['poster']))
                        poster="{{ $mobileHomePromo['poster'] }}"
                    @endif
                    autoplay
                    muted
                    loop
                    playsinline
                    preload="metadata"
                ></video>
            @else
                <img
                    class="mobile-home-promo__image"
                    src="{{ $mobileHomePromo['url'] }}"
                    alt="{{ trans('storefront::storefront.mobile_home_promo.image_alt') }}"
                    loading="lazy"
                >
            @endif
        </{{ $tag }}>
    </div>
</section>
