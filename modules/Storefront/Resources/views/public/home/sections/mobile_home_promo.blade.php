<section class="mobile-home-promo-wrap d-lg-none" aria-label="{{ trans('storefront::storefront.mobile_home_promo.section_label') }}">
    <div class="container">
        @if (($mobileHomePromo['type'] ?? 'image') === 'video')
            <div class="mobile-home-promo mobile-home-promo--video">
                <div class="mobile-home-promo__media">
                    @if (filled($mobileHomePromo['call_to_action_url'] ?? null))
                        <a
                            href="{{ $mobileHomePromo['call_to_action_url'] }}"
                            class="mobile-home-promo__link"
                            target="{{ ($mobileHomePromo['open_in_new_window'] ?? false) ? '_blank' : '_self' }}"
                            @if ($mobileHomePromo['open_in_new_window'] ?? false)
                                rel="noopener noreferrer"
                            @endif
                        >
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
                        </a>
                    @else
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
                    @endif

                    <button
                        type="button"
                        class="mobile-home-promo__sound-toggle"
                        data-mobile-promo-sound-toggle
                        data-label-unmute="{{ trans('storefront::storefront.mobile_home_promo.unmute') }}"
                        data-label-mute="{{ trans('storefront::storefront.mobile_home_promo.mute') }}"
                        aria-pressed="false"
                        aria-label="{{ trans('storefront::storefront.mobile_home_promo.unmute') }}"
                    >
                        <span class="mobile-home-promo__sound-icon mobile-home-promo__sound-icon--muted" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M11 5L6 9H3V15H6L11 19V5Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                                <path d="M15.54 8.46C16.4774 9.39764 17.0039 10.6692 17.0039 11.995C17.0039 13.3208 16.4774 14.5924 15.54 15.53" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                                <path d="M19.07 4.93C20.9447 6.80528 21.9979 9.34836 21.9979 12C21.9979 14.6516 20.9447 17.1947 19.07 19.07" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                                <path d="M3 3L21 21" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="mobile-home-promo__sound-icon mobile-home-promo__sound-icon--unmuted" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M11 5L6 9H3V15H6L11 19V5Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                                <path d="M15.54 8.46C16.4774 9.39764 17.0039 10.6692 17.0039 11.995C17.0039 13.3208 16.4774 14.5924 15.54 15.53" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                                <path d="M19.07 4.93C20.9447 6.80528 21.9979 9.34836 21.9979 12C21.9979 14.6516 20.9447 17.1947 19.07 19.07" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        @else
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
                <img
                    class="mobile-home-promo__image"
                    src="{{ $mobileHomePromo['url'] }}"
                    alt="{{ trans('storefront::storefront.mobile_home_promo.image_alt') }}"
                    loading="lazy"
                >
            </{{ $tag }}>
        @endif
    </div>
</section>
