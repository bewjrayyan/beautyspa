@php
    $statusCode = trim($__env->yieldContent('code')) ?: '500';
    $isAdminRequest = request()->is('admin') || request()->is('admin/*');
    $safeUrl = $isAdminRequest && Route::has('admin.dashboard.index')
        ? route('admin.dashboard.index')
        : (Route::has('home') ? route('home') : url('/'));
    $requestedPath = '/'.ltrim(request()->path(), '/');
    $appName = config('app.name') ?: 'Imma Seri Laris';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#b50863">
    <title>{{ $statusCode }} · @yield('title') | {{ $appName }}</title>
    <style>
        :root {
            color-scheme: light;
            --brand: #b50863;
            --brand-deep: #79033f;
            --brand-soft: #fce7f3;
            --ink: #18131a;
            --muted: #6f6570;
            --line: #eadfe6;
        }

        * { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 28px;
            overflow-x: hidden;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 10%, rgba(181, 8, 99, .16), transparent 34%),
                radial-gradient(circle at 90% 90%, rgba(121, 3, 63, .09), transparent 30%),
                #f8f5f7;
            font-family: Inter, ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .error-shell {
            position: relative;
            width: min(100%, 760px);
        }

        .error-orb {
            position: absolute;
            z-index: 0;
            border-radius: 999px;
            filter: blur(2px);
            pointer-events: none;
        }

        .error-orb--one {
            width: 150px;
            height: 150px;
            top: -52px;
            right: -42px;
            background: linear-gradient(145deg, rgba(181, 8, 99, .28), rgba(181, 8, 99, .03));
        }

        .error-orb--two {
            width: 86px;
            height: 86px;
            bottom: -30px;
            left: -34px;
            background: rgba(121, 3, 63, .10);
        }

        .error-card {
            position: relative;
            z-index: 1;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .9);
            border-radius: 28px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 28px 80px rgba(55, 20, 40, .13);
        }

        .error-accent {
            height: 7px;
            background: linear-gradient(90deg, var(--brand-deep), var(--brand), #e33b91);
        }

        .error-content { padding: 44px 48px 42px; }

        .error-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 34px;
            color: var(--brand-deep);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .error-brand__mark {
            display: inline-grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 11px;
            color: #fff;
            background: linear-gradient(145deg, var(--brand), var(--brand-deep));
            box-shadow: 0 7px 18px rgba(181, 8, 99, .22);
            font-size: 16px;
        }

        .error-heading-row {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .error-code {
            display: inline-grid;
            place-items: center;
            min-width: 112px;
            min-height: 90px;
            padding: 12px 16px;
            border: 1px solid rgba(181, 8, 99, .13);
            border-radius: 20px;
            color: var(--brand);
            background: linear-gradient(145deg, #fff, var(--brand-soft));
            font-size: 38px;
            font-weight: 800;
            letter-spacing: -.05em;
        }

        .error-eyebrow {
            margin: 2px 0 8px;
            color: var(--brand);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(28px, 5vw, 42px);
            line-height: 1.08;
            letter-spacing: -.035em;
        }

        .error-message {
            max-width: 590px;
            margin: 24px 0 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
        }

        .error-path {
            margin-top: 26px;
            padding: 13px 15px;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fbf9fa;
        }

        .error-path span {
            display: block;
            margin-bottom: 5px;
            color: #91838d;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .error-path code {
            display: block;
            overflow: hidden;
            color: #4d4149;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 11px;
            margin-top: 28px;
        }

        .error-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 11px 18px;
            border: 1px solid var(--line);
            border-radius: 12px;
            color: #4d4149;
            background: #fff;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        }

        .error-button:hover,
        .error-button:focus-visible {
            transform: translateY(-1px);
            border-color: rgba(181, 8, 99, .35);
            box-shadow: 0 8px 22px rgba(55, 20, 40, .09);
            outline: none;
        }

        .error-button--primary {
            border-color: var(--brand);
            color: #fff;
            background: linear-gradient(145deg, var(--brand), var(--brand-deep));
            box-shadow: 0 8px 20px rgba(181, 8, 99, .18);
        }

        .error-support {
            margin: 26px 0 0;
            padding-top: 22px;
            border-top: 1px solid var(--line);
            color: #8a7d86;
            font-size: 12px;
            line-height: 1.55;
        }

        @media (max-width: 640px) {
            body { padding: 16px; }
            .error-card { border-radius: 22px; }
            .error-content { padding: 30px 24px 28px; }
            .error-brand { margin-bottom: 26px; }
            .error-heading-row { grid-template-columns: 1fr; gap: 18px; }
            .error-code { min-width: 94px; min-height: 70px; justify-self: start; font-size: 32px; }
            .error-message { margin-top: 20px; font-size: 15px; }
            .error-actions { flex-direction: column; }
            .error-button { width: 100%; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition: none !important; }
        }
    </style>
</head>
<body>
    <main class="error-shell">
        <span class="error-orb error-orb--one" aria-hidden="true"></span>
        <span class="error-orb error-orb--two" aria-hidden="true"></span>

        <section class="error-card" aria-labelledby="error-title">
            <div class="error-accent" aria-hidden="true"></div>
            <div class="error-content">
                <div class="error-brand">
                    <span class="error-brand__mark" aria-hidden="true">I</span>
                    <span>{{ trans('errors.brand_note') }}</span>
                </div>

                <div class="error-heading-row">
                    <div class="error-code" aria-label="Error {{ $statusCode }}">{{ $statusCode }}</div>
                    <div>
                        <p class="error-eyebrow">@yield('eyebrow')</p>
                        <h1 id="error-title">@yield('title')</h1>
                    </div>
                </div>

                <p class="error-message">@yield('message')</p>

                <div class="error-path">
                    <span>{{ trans('errors.requested_page') }}</span>
                    <code>{{ $requestedPath }}</code>
                </div>

                <div class="error-actions">
                    <a href="{{ $safeUrl }}" class="error-button error-button--primary">
                        {{ $isAdminRequest ? trans('errors.dashboard') : trans('errors.home') }}
                    </a>
                    <button type="button" class="error-button" onclick="if (history.length > 1) { history.back(); } else { location.href = @js($safeUrl); }">
                        {{ trans('errors.back') }}
                    </button>
                </div>

                <p class="error-support">{{ trans('errors.support', ['code' => $statusCode]) }}</p>
            </div>
        </section>
    </main>
</body>
</html>
