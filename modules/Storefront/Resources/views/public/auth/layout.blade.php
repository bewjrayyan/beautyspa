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
            window.AestheticCart = window.AestheticCart || {};
            AestheticCart.defaultPhoneCountry = '{{ strtolower(setting('default_country', 'MY')) }}';
            AestheticCart.csrfToken = @json(csrf_token());
            AestheticCart.appUrl = @json(rtrim(config('app.url'), '/'));
            AestheticCart.installPath = @json(parse_url(config('app.url'), PHP_URL_PATH) ?: '');
            AestheticCart.baseUrl = @json(storefront_locale_base_url());
            AestheticCart.locale = @json(locale());
            AestheticCart.url = function (path) {
                const normalizedPath = path.startsWith('/') ? path : `/${path}`;
                const base = (AestheticCart.baseUrl || '').replace(/\/$/, '');

                if (base.startsWith('http://') || base.startsWith('https://')) {
                    return `${base}${normalizedPath}`;
                }

                const appUrl = (AestheticCart.appUrl || '').replace(/\/$/, '');
                const locale = AestheticCart.locale || 'en';

                if (appUrl.startsWith('http://') || appUrl.startsWith('https://')) {
                    return `${appUrl}/${locale}${normalizedPath}`;
                }

                const installPath = (AestheticCart.installPath || '').replace(/\/$/, '');

                return `${window.location.origin}${installPath}/${locale}${normalizedPath}`;
            };
            AestheticCart.apiUrl = function (path) {
                const normalizedPath = path.startsWith('/') ? path : `/${path}`;
                const appUrl = (AestheticCart.appUrl || '').replace(/\/$/, '');

                if (appUrl.startsWith('http://') || appUrl.startsWith('https://')) {
                    return `${appUrl}${normalizedPath}`;
                }

                const installPath = (AestheticCart.installPath || '').replace(/\/$/, '');

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
