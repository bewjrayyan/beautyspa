<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

        <title>
            @yield('title') - {{ setting('store_name') }}
        </title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ font_url(setting('storefront_display_font', 'Rubik')) }}" rel="stylesheet">

        @include('storefront::public.auth.partials.variables')

        <script>
            window.FleetCart = window.FleetCart || {};
            FleetCart.defaultPhoneCountry = '{{ strtolower(setting('default_country', 'MY')) }}';
            FleetCart.csrfToken = @json(csrf_token());
            FleetCart.appUrl = @json(rtrim(config('app.url'), '/'));
            FleetCart.installPath = @json(parse_url(config('app.url'), PHP_URL_PATH) ?: '');
            FleetCart.baseUrl = @json(storefront_locale_base_url());
            FleetCart.locale = @json(locale());
            FleetCart.url = function (path) {
                const normalizedPath = path.startsWith('/') ? path : `/${path}`;
                const base = (FleetCart.baseUrl || '').replace(/\/$/, '');

                if (base.startsWith('http://') || base.startsWith('https://')) {
                    return `${base}${normalizedPath}`;
                }

                const appUrl = (FleetCart.appUrl || '').replace(/\/$/, '');
                const locale = FleetCart.locale || 'en';

                if (appUrl.startsWith('http://') || appUrl.startsWith('https://')) {
                    return `${appUrl}/${locale}${normalizedPath}`;
                }

                const installPath = (FleetCart.installPath || '').replace(/\/$/, '');

                return `${window.location.origin}${installPath}/${locale}${normalizedPath}`;
            };
            FleetCart.apiUrl = function (path) {
                const normalizedPath = path.startsWith('/') ? path : `/${path}`;
                const appUrl = (FleetCart.appUrl || '').replace(/\/$/, '');

                if (appUrl.startsWith('http://') || appUrl.startsWith('https://')) {
                    return `${appUrl}${normalizedPath}`;
                }

                const installPath = (FleetCart.installPath || '').replace(/\/$/, '');

                return `${window.location.origin}${installPath}${normalizedPath}`;
            };
        </script>

        @vite([
            'modules/Storefront/Resources/assets/public/sass/pages/auth/main.scss',
            'modules/Storefront/Resources/assets/public/js/pages/auth/main.js',
        ])

        @stack('globals')
    </head>

    <body class="clearfix {{ is_rtl() ? 'rtl' : 'ltr' }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
        <div class="login-page">
            @yield('content')
        </div>

        @stack('scripts')
    </body>
</html>
