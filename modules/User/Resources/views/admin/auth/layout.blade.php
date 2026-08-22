<!DOCTYPE html>
<html>
    <head>
        <base href="{{ rtrim(config('app.url'), '/') }}/">
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

        <title>
            @yield('title') - {{ setting('store_name') }}
        </title>

        @php
            $authLoginBg = null;

            try {
                $authLoginBg = \Illuminate\Support\Facades\Vite::asset(
                    'modules/User/Resources/assets/admin/images/login-page-bg.webp'
                );
            } catch (\Throwable $e) {
                $authLoginBg = null;
            }
        @endphp

        @if ($authLoginBg)
            <link rel="preload" as="image" href="{{ $authLoginBg }}" type="image/webp" fetchpriority="high">
        @endif

        @vite([
            'modules/User/Resources/assets/admin/sass/auth/main.scss',
            'modules/User/Resources/assets/admin/js/auth/main.js',
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
