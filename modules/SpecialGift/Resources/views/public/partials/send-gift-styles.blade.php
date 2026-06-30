@push('globals')
    <style>
        .page-send-gift .subscribe-wrap {
            display: none;
        }

        .sg-page {
            --sg-accent: #f274ac;
            position: relative;
            overflow: hidden;
            padding: 28px 0 72px;
            background:
                radial-gradient(ellipse 80% 60% at 50% -10%, color-mix(in srgb, var(--sg-accent) 35%, transparent), transparent 60%),
                linear-gradient(180deg, color-mix(in srgb, var(--sg-accent) 8%, #fff) 0%, #fff 42%, color-mix(in srgb, var(--sg-accent) 6%, #fff) 100%);
        }

        .sg-page--no-gradient {
            background: #fff;
        }

        .sg-page--no-bokeh .sg-page__orb {
            display: none;
        }

        .sg-page--no-sparkles .sg-page__sparkle {
            display: none;
        }

        .sg-page__bg {
            pointer-events: none;
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        .sg-page__orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.55;
        }

        .sg-page__orb--one {
            top: -40px;
            right: -60px;
            width: 280px;
            height: 280px;
            background: color-mix(in srgb, var(--sg-accent) 72%, #fff);
        }

        .sg-page__orb--two {
            bottom: 10%;
            left: -80px;
            width: 240px;
            height: 240px;
            background: color-mix(in srgb, var(--sg-accent) 48%, #fff);
        }

        .sg-page__orb--three {
            top: 35%;
            right: 12%;
            width: 160px;
            height: 160px;
            background: color-mix(in srgb, var(--sg-accent) 28%, #fde68a);
            opacity: 0.35;
        }

        .sg-page__sparkle {
            position: absolute;
            color: var(--sg-accent);
            opacity: 0.25;
            font-size: 22px;
            animation: sg-float 6s ease-in-out infinite;
        }

        .sg-page__sparkle--1 { top: 12%; left: 8%; animation-delay: 0s; }
        .sg-page__sparkle--2 { top: 22%; right: 14%; font-size: 18px; animation-delay: 1.5s; }
        .sg-page__sparkle--3 { bottom: 18%; right: 22%; animation-delay: 3s; }

        @keyframes sg-float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(8deg); }
        }

        .sg-page__container {
            position: relative;
            z-index: 1;
        }

        .sg-hero {
            max-width: 720px;
            margin: 0 auto 36px;
            text-align: center;
        }

        .sg-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 14px;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            color: color-mix(in srgb, var(--sg-accent) 78%, #000);
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid color-mix(in srgb, var(--sg-accent) 22%, transparent);
            box-shadow: 0 8px 24px color-mix(in srgb, var(--sg-accent) 12%, transparent);
        }

        .sg-hero__title {
            margin: 0 0 12px;
            font-size: clamp(28px, 4vw, 42px);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            color: #1f2937;
            background: linear-gradient(135deg, color-mix(in srgb, var(--sg-accent) 88%, #000) 0%, var(--sg-accent) 45%, color-mix(in srgb, var(--sg-accent) 82%, #fff) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sg-hero__lead {
            margin: 0 auto 28px;
            max-width: 560px;
            font-size: 16px;
            line-height: 1.65;
            color: #6b7280;
        }

        .sg-steps {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .sg-steps__item {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid color-mix(in srgb, var(--sg-accent) 14%, transparent);
        }

        .sg-steps__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, var(--sg-accent), color-mix(in srgb, var(--sg-accent) 82%, #000));
            box-shadow: 0 4px 12px color-mix(in srgb, var(--sg-accent) 35%, transparent);
        }

        .sg-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
            gap: 28px;
            align-items: start;
            max-width: 1040px;
            margin: 0 auto;
        }

        .sg-preview__card {
            position: relative;
            padding: 22px 22px 20px;
            border-radius: 24px;
            background: linear-gradient(160deg, #fff 0%, #fff7fb 100%);
            border: 1px solid color-mix(in srgb, var(--sg-accent) 16%, transparent);
            box-shadow:
                0 24px 60px rgba(190, 24, 93, 0.12),
                0 2px 0 rgba(255, 255, 255, 0.9) inset;
        }

        .sg-preview__ribbon {
            position: absolute;
            top: 18px;
            right: -6px;
            width: 88px;
            height: 28px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            border-radius: 4px 0 0 4px;
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);
        }

        .sg-preview__ribbon::after {
            content: "";
            position: absolute;
            right: 0;
            bottom: -6px;
            border: 6px solid transparent;
            border-top-color: #b45309;
            border-right-color: #b45309;
        }

        .sg-preview__label {
            margin: 0 0 14px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #9d174d;
        }

        .sg-preview__frame {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.14);
        }

        .sg-preview__image {
            display: block;
            width: 100%;
            height: auto;
            aspect-ratio: 1200 / 630;
            object-fit: cover;
        }

        .sg-preview__overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
            pointer-events: none;
        }

        .sg-preview__name,
        .sg-preview__order {
            margin: 0;
            color: #fff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.45);
            font-weight: 700;
        }

        .sg-preview__name {
            font-size: clamp(18px, 2.5vw, 26px);
            line-height: 1.2;
        }

        .sg-preview__order {
            margin-top: 8px;
            font-size: clamp(13px, 1.8vw, 16px);
            opacity: 0.95;
        }

        .sg-preview__trust {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 16px 0 0;
            font-size: 13px;
            line-height: 1.5;
            color: #6b7280;
        }

        .sg-preview__trust svg {
            flex-shrink: 0;
            margin-top: 2px;
            color: var(--sg-accent);
        }

        .sg-panel .alert {
            margin-bottom: 16px;
            border-radius: 14px;
        }

        .sg-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 16px;
            padding: 14px 16px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.5;
        }

        .sg-alert--error {
            color: #991b1b;
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        .sg-alert--success {
            color: #065f46;
            background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
            border: 1px solid #a7f3d0;
        }

        .sg-alert__icon {
            font-size: 18px;
            line-height: 1;
        }

        .sg-form-card {
            padding: 28px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid color-mix(in srgb, var(--sg-accent) 14%, transparent);
            box-shadow: 0 20px 50px rgba(17, 24, 39, 0.08);
            backdrop-filter: blur(8px);
        }

        .sg-form-card__title {
            margin: 0 0 22px;
            font-size: 22px;
            font-weight: 800;
            color: #111827;
        }

        .sg-field {
            margin-bottom: 18px;
        }

        .sg-field__label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        .sg-field__required {
            color: #e11d48;
        }

        .sg-field__control {
            position: relative;
        }

        .sg-field__icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: var(--sg-accent);
            opacity: 0.75;
            pointer-events: none;
        }

        .sg-input {
            width: 100%;
            padding: 14px 14px 14px 44px;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            font-size: 15px;
            color: #111827;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .sg-input::placeholder {
            color: #9ca3af;
        }

        .sg-input:hover {
            border-color: #f9a8d4;
        }

        .sg-input:focus {
            outline: none;
            border-color: var(--sg-accent);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--sg-accent) 16%, transparent);
        }

        .sg-field__hint {
            margin: 8px 0 0;
            font-size: 12px;
            line-height: 1.5;
            color: #9ca3af;
        }

        .sg-submit {
            position: relative;
            overflow: hidden;
            width: 100%;
            margin-top: 8px;
            padding: 0;
            border: 0;
            border-radius: 16px;
            cursor: pointer;
            background: linear-gradient(135deg, var(--sg-accent) 0%, color-mix(in srgb, var(--sg-accent) 82%, #000) 52%, color-mix(in srgb, var(--sg-accent) 70%, #000) 100%);
            box-shadow: 0 14px 30px rgba(219, 39, 119, 0.35);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .sg-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(219, 39, 119, 0.42);
        }

        .sg-submit:disabled {
            opacity: 0.75;
            cursor: wait;
        }

        .sg-submit__shine {
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.22) 50%, transparent 60%);
            transform: translateX(-120%);
            animation: sg-shine 4s ease-in-out infinite;
        }

        @keyframes sg-shine {
            0%, 70%, 100% { transform: translateX(-120%); }
            85% { transform: translateX(120%); }
        }

        .sg-submit__content {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px 20px;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }

        .sg-submit__icon {
            flex-shrink: 0;
        }

        @media (max-width: 991px) {
            .sg-layout {
                grid-template-columns: 1fr;
            }

            .sg-preview {
                order: -1;
            }
        }

        @media (max-width: 575px) {
            .sg-page {
                padding-top: 16px;
            }

            .sg-hero__eyebrow {
                font-size: 12px;
            }

            .sg-steps__item span:last-child {
                font-size: 12px;
            }

            .sg-form-card {
                padding: 22px 18px;
            }
        }
    </style>
@endpush
