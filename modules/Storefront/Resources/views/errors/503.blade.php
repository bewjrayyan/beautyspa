@php
    $logoId = function_exists('storefront_header_logo_id') ? storefront_header_logo_id() : null;
    $logoUrl = $logoId ? absolute_public_url(\Modules\Media\Entities\File::find($logoId)?->path) : null;
    $storeName = (string) (setting('store_name') ?: config('app.name'));
    $themeColor = (string) (setting('storefront_theme_color') ?: '#e91e8c');
    $en = require module_path('Storefront', 'Resources/lang/en/maintenance.php');
    $ms = require module_path('Storefront', 'Resources/lang/ms/maintenance.php');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $en['title'] }} | {{ $storeName }}</title>
    <style>
        :root {
            --brand: {{ $themeColor }};
            --brand-deep: color-mix(in srgb, var(--brand) 72%, #4a0d2e);
            --brand-mid: color-mix(in srgb, var(--brand) 55%, #ffffff);
            --brand-soft: color-mix(in srgb, var(--brand) 18%, white);
            --brand-glow: color-mix(in srgb, var(--brand) 35%, white);
            --text: #1f1020;
            --muted: #6b5a66;
            --card: rgba(255, 255, 255, 0.88);
            --card-border: rgba(255, 255, 255, 0.65);
            --shadow: rgba(74, 13, 46, 0.18);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow: hidden;
            position: relative;
            background:
                radial-gradient(circle at 18% 22%, color-mix(in srgb, var(--brand) 55%, white), transparent 34%),
                radial-gradient(circle at 82% 12%, rgba(255, 255, 255, 0.28), transparent 28%),
                radial-gradient(circle at 50% 100%, color-mix(in srgb, var(--brand-deep) 55%, #2b0a1d), transparent 52%),
                linear-gradient(145deg, var(--brand-deep) 0%, var(--brand) 42%, var(--brand-mid) 100%);
        }

        .maintenance-scene {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }

        .maintenance-scene::before {
            content: "";
            position: absolute;
            inset: -20%;
            background:
                radial-gradient(circle at 30% 40%, rgba(255, 255, 255, 0.22), transparent 24%),
                radial-gradient(circle at 70% 60%, rgba(255, 255, 255, 0.14), transparent 28%);
            animation: scene-drift 14s ease-in-out infinite alternate;
        }

        .maintenance-scene::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.18) 0.6px, transparent 0.6px);
            background-size: 3px 3px;
            opacity: 0.25;
            mix-blend-mode: soft-light;
        }

        .maintenance-bokeh span {
            position: absolute;
            display: block;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.35) 28%, rgba(255, 255, 255, 0) 72%);
            filter: blur(2px);
            opacity: 0;
            animation: bokeh-float linear infinite;
        }

        .maintenance-bokeh span:nth-child(1)  { width: 120px; height: 120px; left: 8%;  top: 18%; animation-duration: 11s; animation-delay: -1s; }
        .maintenance-bokeh span:nth-child(2)  { width: 180px; height: 180px; left: 72%; top: 10%; animation-duration: 13s; animation-delay: -4s; }
        .maintenance-bokeh span:nth-child(3)  { width: 90px;  height: 90px;  left: 58%; top: 62%; animation-duration: 9s;  animation-delay: -2s; }
        .maintenance-bokeh span:nth-child(4)  { width: 220px; height: 220px; left: 18%; top: 68%; animation-duration: 15s; animation-delay: -6s; }
        .maintenance-bokeh span:nth-child(5)  { width: 70px;  height: 70px;  left: 84%; top: 72%; animation-duration: 8s;  animation-delay: -3s; }
        .maintenance-bokeh span:nth-child(6)  { width: 140px; height: 140px; left: 42%; top: 28%; animation-duration: 12s; animation-delay: -5s; }
        .maintenance-bokeh span:nth-child(7)  { width: 55px;  height: 55px;  left: 28%; top: 42%; animation-duration: 7s;  animation-delay: -1.5s; }
        .maintenance-bokeh span:nth-child(8)  { width: 160px; height: 160px; left: 64%; top: 38%; animation-duration: 10s; animation-delay: -7s; }
        .maintenance-bokeh span:nth-child(9)  { width: 48px;  height: 48px;  left: 12%; top: 78%; animation-duration: 6s;  animation-delay: -2.5s; }
        .maintenance-bokeh span:nth-child(10) { width: 100px; height: 100px; left: 88%; top: 34%; animation-duration: 11s; animation-delay: -8s; }
        .maintenance-bokeh span:nth-child(11) { width: 76px;  height: 76px;  left: 50%; top: 8%;  animation-duration: 9s;  animation-delay: -4.5s; }
        .maintenance-bokeh span:nth-child(12) { width: 130px; height: 130px; left: 4%;  top: 52%; animation-duration: 14s; animation-delay: -9s; }

        .maintenance-shimmer {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(115deg, transparent 35%, rgba(255, 255, 255, 0.16) 50%, transparent 65%);
            transform: translateX(-120%);
            animation: shimmer-sweep 7s ease-in-out infinite;
        }

        .maintenance-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 520px;
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            box-shadow:
                0 24px 60px var(--shadow),
                inset 0 1px 0 rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            padding: 40px 32px 36px;
            text-align: center;
        }

        .maintenance-logo {
            max-height: 56px;
            max-width: 220px;
            margin: 0 auto 24px;
            display: block;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(255, 255, 255, 0.35));
        }

        .maintenance-store {
            margin: 0 0 8px;
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--brand);
        }

        .maintenance-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: linear-gradient(145deg, var(--brand-soft), rgba(255, 255, 255, 0.9));
            color: var(--brand);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                0 10px 24px color-mix(in srgb, var(--brand) 22%, transparent),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .maintenance-icon svg {
            width: 34px;
            height: 34px;
        }

        .maintenance-heading {
            margin: 0 0 12px;
            font-size: 1.75rem;
            line-height: 1.25;
            font-weight: 700;
        }

        .maintenance-message {
            margin: 0 0 10px;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.6;
        }

        .maintenance-thanks {
            margin: 0;
            color: var(--muted);
            font-size: 0.9375rem;
        }

        .maintenance-locale {
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid color-mix(in srgb, var(--brand) 12%, #e2e8f0);
            text-align: left;
        }

        .maintenance-locale__label {
            display: inline-block;
            margin-bottom: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--brand);
        }

        .maintenance-locale__heading {
            margin: 0 0 8px;
            font-size: 1.125rem;
            font-weight: 700;
        }

        .maintenance-locale__message,
        .maintenance-locale__thanks {
            margin: 0 0 8px;
            color: var(--muted);
            line-height: 1.6;
        }

        .maintenance-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            padding: 8px 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--brand-soft), rgba(255, 255, 255, 0.85));
            color: var(--brand);
            font-size: 0.8125rem;
            font-weight: 600;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .maintenance-status::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            box-shadow: 0 0 10px currentColor;
            animation: pulse 1.4s ease-in-out infinite;
        }

        @keyframes bokeh-float {
            0% {
                opacity: 0;
                transform: translate3d(0, 18px, 0) scale(0.82);
            }

            18% {
                opacity: 0.85;
            }

            55% {
                opacity: 0.55;
                transform: translate3d(12px, -28px, 0) scale(1.05);
            }

            100% {
                opacity: 0;
                transform: translate3d(-10px, -56px, 0) scale(1.12);
            }
        }

        @keyframes shimmer-sweep {
            0%, 18% {
                transform: translateX(-120%);
                opacity: 0;
            }

            35% {
                opacity: 0.75;
            }

            55%, 100% {
                transform: translateX(120%);
                opacity: 0;
            }
        }

        @keyframes scene-drift {
            0% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            100% {
                transform: translate3d(-2%, 2%, 0) scale(1.05);
            }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.45; transform: scale(0.85); }
        }

        @media (prefers-reduced-motion: reduce) {
            .maintenance-bokeh span,
            .maintenance-shimmer,
            .maintenance-scene::before {
                animation: none;
            }

            .maintenance-bokeh span {
                opacity: 0.45;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-scene" aria-hidden="true">
        <div class="maintenance-bokeh">
            <span></span><span></span><span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span><span></span><span></span>
        </div>
        <div class="maintenance-shimmer"></div>
    </div>

    <main class="maintenance-card" role="main">
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $storeName }}" class="maintenance-logo">
        @else
            <p class="maintenance-store">{{ $storeName }}</p>
        @endif

        <div class="maintenance-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
        </div>

        <h1 class="maintenance-heading">{{ $en['heading'] }}</h1>
        <p class="maintenance-message">{{ $en['message'] }}</p>
        <p class="maintenance-thanks">{{ $en['thanks'] }}</p>

        <div class="maintenance-locale" lang="ms">
            <span class="maintenance-locale__label">Bahasa Malaysia</span>
            <h2 class="maintenance-locale__heading">{{ $ms['heading'] }}</h2>
            <p class="maintenance-locale__message">{{ $ms['message'] }}</p>
            <p class="maintenance-locale__thanks">{{ $ms['thanks'] }}</p>
        </div>

        <div class="maintenance-status">503 · {{ $en['title'] }}</div>
    </main>
</body>
</html>
