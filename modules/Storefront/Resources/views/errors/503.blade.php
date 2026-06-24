@php
    use Modules\Setting\Support\MaintenancePageSettings;

    $fx = MaintenancePageSettings::resolved();
    $logoId = function_exists('storefront_header_logo_id') ? storefront_header_logo_id() : null;
    $logoUrl = $logoId ? absolute_public_url(\Modules\Media\Entities\File::find($logoId)?->path) : null;
    $storeName = (string) (setting('store_name') ?: config('app.name'));
    $themeColor = $fx['theme_color'];
    $en = require module_path('Storefront', 'Resources/lang/en/maintenance.php');
    $ms = require module_path('Storefront', 'Resources/lang/ms/maintenance.php');
    $bokehOrbs = $fx['bokeh'] ? MaintenancePageSettings::bokehOrbs($fx['bokeh_count']) : [];

    $bodyClasses = collect([
        'maintenance-page',
        $fx['gradient'] ? 'fx-gradient' : 'fx-gradient-off',
        $fx['bokeh'] ? 'fx-bokeh' : 'fx-bokeh-off',
        $fx['shimmer'] ? 'fx-shimmer' : 'fx-shimmer-off',
        $fx['grain_drift'] ? 'fx-grain-drift' : 'fx-grain-drift-off',
        $fx['frosted_card'] ? 'fx-frosted-card' : 'fx-frosted-card-off',
    ])->implode(' ');
@endphp
<!-- maintenance-fx:{{ MaintenancePageSettings::fingerprint() }} -->
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
            background: #f1f5f9;
        }

        body.fx-gradient {
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

        body.fx-grain-drift .maintenance-scene::before {
            content: "";
            position: absolute;
            inset: -20%;
            background:
                radial-gradient(circle at 30% 40%, rgba(255, 255, 255, 0.22), transparent 24%),
                radial-gradient(circle at 70% 60%, rgba(255, 255, 255, 0.14), transparent 28%);
            animation: scene-drift 14s ease-in-out infinite alternate;
        }

        body.fx-grain-drift .maintenance-scene::after {
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

        body.fx-bokeh-off .maintenance-bokeh {
            display: none;
        }

        .maintenance-shimmer {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(115deg, transparent 35%, rgba(255, 255, 255, 0.16) 50%, transparent 65%);
            transform: translateX(-120%);
            animation: shimmer-sweep 7s ease-in-out infinite;
        }

        body.fx-shimmer-off .maintenance-shimmer {
            display: none;
        }

        .maintenance-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 520px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
            padding: 40px 32px 36px;
            text-align: center;
        }

        body.fx-frosted-card .maintenance-card {
            background: var(--card);
            border: 1px solid var(--card-border);
            box-shadow:
                0 24px 60px var(--shadow),
                inset 0 1px 0 rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
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
<body class="{{ $bodyClasses }}">
    <div class="maintenance-scene" aria-hidden="true">
        @if ($fx['bokeh'])
            <div class="maintenance-bokeh">
                @foreach ($bokehOrbs as $orb)
                    <span style="width: {{ $orb['width'] }}px; height: {{ $orb['height'] }}px; left: {{ $orb['left'] }}; top: {{ $orb['top'] }}; animation-duration: {{ $orb['duration'] }}s; animation-delay: {{ $orb['delay'] }}s;"></span>
                @endforeach
            </div>
        @endif

        @if ($fx['shimmer'])
            <div class="maintenance-shimmer"></div>
        @endif
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
