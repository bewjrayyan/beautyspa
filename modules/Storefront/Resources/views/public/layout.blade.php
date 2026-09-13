<!DOCTYPE html>
<html lang="{{ locale() }}" prefix="og: https://ogp.me/ns#">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">

        @if ($favicon)
            <link rel="icon" href="{{ $favicon }}" type="{{ $faviconMime }}" sizes="32x32">
            <link rel="shortcut icon" href="{{ $favicon }}" type="{{ $faviconMime }}">
            <link rel="apple-touch-icon" href="{{ $faviconTouch ?: $favicon }}">
        @endif

        @if (! empty($seoToolsMeta ?? null))
            {!! $seoToolsMeta !!}
        @else
            <title>
                @hasSection('title')
                    @yield('title') - {{ setting('store_name') }}
                @else
                    @if (setting('store_tagline'))
                        {{ setting('store_tagline') }} -
                    @endif

                    {{ setting('store_name') }}
                @endif
            </title>
        @endif

        @stack('meta')

        @if (empty($seoToolsMeta ?? null) && empty($seoMetaRendered ?? null) && ! empty($openGraph))
            @include('meta::public.open_graph', ['openGraph' => $openGraph])
        @endif

        @PWA

        @include('storefront::public.partials.performance_head')

        @vite([font_vite_entry(setting('storefront_display_font', 'Poppins'))])

        @include('storefront::public.partials.variables')

        @vite([
            'modules/Storefront/Resources/assets/public/sass/vendors/_bootstrap.scss',
            'modules/Storefront/Resources/assets/public/sass/vendors/_line-awesome.scss',
            'modules/Storefront/Resources/assets/public/sass/vendors/_swiper.scss',
            'modules/Storefront/Resources/assets/public/sass/vendors/_toastify.scss',
            'modules/Storefront/Resources/assets/public/sass/app.scss',
            'modules/Storefront/Resources/assets/public/js/app.js',
            'modules/Storefront/Resources/assets/public/js/main.js'
        ])

        @stack('styles')

        {!! sanitize_custom_assets(setting('custom_header_assets')) !!}

        <script>
            window.AestheticCart = {
                appUrl: @json(rtrim(config('app.url'), '/')),
                installPath: @json(parse_url(config('app.url'), PHP_URL_PATH) ?: ''),
                baseUrl: @json(storefront_locale_base_url()),
                url(path) {
                    const normalizedPath = path.startsWith('/') ? path : `/${path}`;
                    const base = (this.baseUrl || '').replace(/\/$/, '');

                    if (base.startsWith('http://') || base.startsWith('https://')) {
                        return `${base}${normalizedPath}`;
                    }

                    const appUrl = (this.appUrl || '').replace(/\/$/, '');
                    const locale = this.locale || 'en';

                    if (appUrl.startsWith('http://') || appUrl.startsWith('https://')) {
                        return `${appUrl}/${locale}${normalizedPath}`;
                    }

                    const installPath = (this.installPath || '').replace(/\/$/, '');

                    return `${window.location.origin}${installPath}/${locale}${normalizedPath}`;
                },
                apiUrl(path) {
                    const normalizedPath = path.startsWith('/') ? path : `/${path}`;
                    const appUrl = (this.appUrl || '').replace(/\/$/, '');

                    if (appUrl.startsWith('http://') || appUrl.startsWith('https://')) {
                        return `${appUrl}${normalizedPath}`;
                    }

                    const installPath = (this.installPath || '').replace(/\/$/, '');

                    return `${window.location.origin}${installPath}${normalizedPath}`;
                },
                assetUrl(path) {
                    const normalizedPath = path.startsWith('/') ? path : `/${path}`;
                    const appUrl = (this.appUrl || '').replace(/\/$/, '');
                    const installPath = (this.installPath || '').replace(/\/$/, '');

                    if (appUrl.startsWith('http://') || appUrl.startsWith('https://')) {
                        return `${appUrl}${installPath}${normalizedPath}`;
                    }

                    return `${window.location.origin}${installPath}${normalizedPath}`;
                },
                rtl: {{ is_rtl() ? 'true' : 'false' }},
                storeName: '{{ setting('store_name') }}',
                storeLogo: '{{ $logo }}',
                currency: '{{ currency() }}',
                locale: '{{ locale() }}',
                defaultPhoneCountry: '{{ strtolower(setting('default_country', 'MY')) }}',
                supportedLocales: @json(supported_locales()),
                loggedIn: {{ auth()->check() ? 'true' : 'false' }},
                compareCount: {{ $compareCount }},
                cartQuantity: {{ $cartQuantity }},
                wishlistCount: {{ $wishlistCount }},
                csrfToken: '{{ csrf_token() }}',
                data: {},
                langs: {
                    'storefront::storefront.something_went_wrong': '{{ trans('storefront::storefront.something_went_wrong') }}',
                    'storefront::layouts.more_results': '{{ trans('storefront::layouts.more_results') }}',
                    'storefront::product_card.price_range': '{{ trans('storefront::product_card.price_range') }}',
                    'storefront::product_card.normal_price': '{{ trans('storefront::product_card.normal_price') }}',
                    'storefront::cart.save_amount': @json(storefront_js_trans('storefront::cart.save_amount')),
                                        'storefront::cart.save_amount_with_percent': @json(storefront_js_trans('storefront::cart.save_amount_with_percent')),
                    'storefront::pwa.title': @json(trans('storefront::pwa.title')),
                    'storefront::pwa.ios_title': @json(trans('storefront::pwa.ios_title')),
                    'storefront::pwa.android_title': @json(trans('storefront::pwa.android_title')),
                    'storefront::pwa.desktop_title': @json(trans('storefront::pwa.desktop_title')),
                    'storefront::pwa.ios_step_1': @json(trans('storefront::pwa.ios_step_1')),
                    'storefront::pwa.ios_step_2': @json(trans('storefront::pwa.ios_step_2')),
                    'storefront::pwa.ios_step_3': @json(trans('storefront::pwa.ios_step_3')),
                    'storefront::pwa.android_step_1': @json(trans('storefront::pwa.android_step_1')),
                    'storefront::pwa.android_step_2': @json(trans('storefront::pwa.android_step_2')),
                    'storefront::pwa.desktop_step_1': @json(trans('storefront::pwa.desktop_step_1')),
                    'storefront::pwa.desktop_step_2': @json(trans('storefront::pwa.desktop_step_2')),
                },
            };
        </script>

        {!! $schemaMarkup->toScript() !!}

        @stack('globals')
    </head>

    <body
        dir="{{ is_rtl() ? 'rtl' : 'ltr' }}"
        class="page-template {{ is_rtl() ? 'rtl' : 'ltr' }} @yield('body_class')"
        data-theme-color="{{ $themeColor->toHexString() }}"
    >
        <div x-data="App" class="wrapper">
            @include('storefront::public.layouts.top_nav')
            @include('storefront::public.layouts.header')
            @include('storefront::public.layouts.navigation')
            @include('storefront::public.layouts.breadcrumb')

            @yield('content')

            @include('storefront::public.home.sections.newsletter_subscription')
            @include('storefront::public.layouts.footer')

            <div
                class="overlay"
                :class="{ active: $store.layout.overlay }"
                @click="hideOverlay"
            >
            </div>

            @include('storefront::public.layouts.sidebar_menu')
            @include('storefront::public.layouts.localization')

            @if (!request()->routeIs('checkout.create'))
                @include('storefront::public.layouts.sidebar_cart')
            @endif

            @include('storefront::public.layouts.alert')
            @include('storefront::public.layouts.newsletter_popup')
            @include('storefront::public.layouts.pwa_install_modal')
            @include('storefront::public.layouts.cookie_bar')
            @include('storefront::public.layouts.scroll_to_top')
        </div>

        @stack('pre-scripts')
        @stack('scripts')

        <script type="module">
            // Homepage splits below-fold Alpine components into async chunks.
            // Wait so x-data components are registered before Alpine walks the DOM.
            if (window.__storefrontPageReady) {
                await window.__storefrontPageReady;
            }

            Alpine.start();
        </script>

        {!! sanitize_custom_assets(setting('custom_footer_assets')) !!}
    </body>
</html>
