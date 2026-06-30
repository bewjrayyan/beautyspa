@extends('storefront::public.layout')

@section('title', trans('storefront::contact.contact'))

@section('content')
    @php
        $spaBranches = $spaBranches ?? collect();
        $hasSpaBranches = $spaBranches->isNotEmpty();
        $mapAddress = $mapAddress ?? setting('storefront_address');
    @endphp

    <section class="contact-wrap contact-wrap--modern{{ $hasSpaBranches ? ' contact-wrap--has-branches' : '' }}">
        <div class="container">
            <header class="contact-page-header">
                <span class="contact-page-header__eyebrow">{{ trans('storefront::contact.eyebrow') }}</span>
                <h1 class="contact-page-header__title">{{ trans('storefront::contact.contact') }}</h1>
                <p class="contact-page-header__lead">
                    {{ $hasSpaBranches ? trans('storefront::contact.page_lead_branches') : trans('storefront::contact.page_lead') }}
                </p>
            </header>

            @if (filled($mapAddress))
                <div class="contact-map-card">
                    <div class="contact-map-card__header">
                        <div class="contact-map-card__heading">
                            <span class="contact-map-card__icon" aria-hidden="true">
                                <i class="las la-map-marked-alt"></i>
                            </span>
                            <div>
                                <h2 class="contact-map-card__title">{{ trans('storefront::contact.find_us') }}</h2>
                                <p class="contact-map-card__subtitle">{{ trans('storefront::contact.map_subtitle') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-map-card__canvas map-canvas">
                        <iframe
                            title="{{ trans('storefront::contact.find_us') }}"
                            src="https://maps.google.com/maps?q={{ urlencode($mapAddress) }}&t=&z=13&ie=UTF8&iwloc=&output=embed"
                            frameborder="0"
                            scrolling="no"
                            marginheight="0"
                            marginwidth="0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>
                </div>
            @endif

            @include('storefront::public.contact.partials.spa_branches', ['spaBranches' => $spaBranches])

            <div class="contact-layout">
                <aside class="contact-aside">
                    <div class="contact-aside__card">
                        <h2 class="contact-aside__title">{{ trans('storefront::contact.get_in_touch') }}</h2>

                        @if (! $hasSpaBranches)
                            <ul class="contact-aside__list">
                                @if (setting('store_phone') && ! setting('store_phone_hide'))
                                    <li class="contact-aside__item">
                                        <span class="contact-aside__icon" aria-hidden="true">
                                            <i class="las la-phone"></i>
                                        </span>
                                        <span class="contact-aside__body">
                                            <span class="contact-aside__label">{{ trans('storefront::contact.phone') }}</span>
                                            <a href="tel:{{ preg_replace('/\s+/', '', setting('store_phone')) }}" class="store-phone">
                                                <span>{{ substr(setting('store_phone'), 0, strlen(setting('store_phone')) / 2) }}</span>
                                                <span class="d-none">JUNK LOAD</span>
                                                <span>{{ substr(setting('store_phone'), strlen(setting('store_phone')) / 2) }}</span>
                                            </a>
                                        </span>
                                    </li>
                                @endif

                                @if (! setting('store_email_hide'))
                                    <li class="contact-aside__item">
                                        <span class="contact-aside__icon" aria-hidden="true">
                                            <i class="las la-envelope"></i>
                                        </span>
                                        <span class="contact-aside__body">
                                            <span class="contact-aside__label">{{ trans('storefront::contact.email') }}</span>
                                            <a href="mailto:{{ setting('store_email') }}" class="store-email">
                                                <span>{{ substr(setting('store_email'), 0, strlen(setting('store_email')) / 2) }}</span>
                                                <span class="d-none">JUNK LOAD</span>
                                                <span>{{ substr(setting('store_email'), strlen(setting('store_email')) / 2) }}</span>
                                            </a>
                                        </span>
                                    </li>
                                @endif

                                @if (setting('storefront_address'))
                                    <li class="contact-aside__item">
                                        <span class="contact-aside__icon" aria-hidden="true">
                                            <i class="las la-map-marker-alt"></i>
                                        </span>
                                        <span class="contact-aside__body">
                                            <span class="contact-aside__label">{{ trans('storefront::contact.address') }}</span>
                                            <span class="contact-aside__text">{{ setting('storefront_address') }}</span>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        @else
                            <p class="contact-aside__lead">{{ trans('storefront::contact.branches_lead') }}</p>
                        @endif

                        @if (social_links()->isNotEmpty())
                            <div class="contact-aside__social">
                                <span class="contact-aside__social-label">{{ trans('storefront::contact.follow_us') }}</span>
                                <ul class="list-inline social-links contact-aside__social-links">
                                    @foreach (social_links() as $icon => $socialLink)
                                        <li>
                                            <a href="{{ $socialLink }}" title="{{ social_link_name($icon) }}" target="_blank" rel="noopener noreferrer">
                                                @if ($icon === 'lab la-twitter')
                                                    <svg class="twitter-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" width="30px" height="30px" aria-hidden="true">
                                                        <path d="M26.37,26l-8.795-12.822l0.015,0.012L25.52,4h-2.65l-6.46,7.48L11.28,4H4.33l8.211,11.971L12.54,15.97L3.88,26h2.65 l7.182-8.322L19.42,26H26.37z M10.23,6l12.34,18h-2.1L8.12,6H10.23z"/>
                                                    </svg>
                                                @else
                                                    <i class="{{ $icon }}" aria-hidden="true"></i>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </aside>

                <div class="contact-main-card">
                    <div class="contact-main-card__header">
                        <span class="contact-main-card__icon" aria-hidden="true">
                            <i class="las la-comment-dots"></i>
                        </span>
                        <div>
                            <h2 class="contact-main-card__title">{{ trans('storefront::contact.leave_a_message') }}</h2>
                            <p class="contact-main-card__subtitle">{{ trans('storefront::contact.form_subtitle') }}</p>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="contact-alert contact-alert--success" role="status">
                            <i class="las la-check-circle" aria-hidden="true"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="contact-form">
                        <form method="POST" action="{{ route('contact.store') }}" @include('storefront::public.partials.google_recaptcha_form_attrs', ['action' => 'contact'])>
                            @csrf
                            @honeypot

                            <div class="contact-form__grid">
                                <div class="form-group">
                                    <label for="email">
                                        {{ trans('contact::attributes.email') }}<span>*</span>
                                    </label>

                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        id="email"
                                        class="form-control"
                                        autocomplete="email"
                                        placeholder="{{ trans('storefront::contact.email_placeholder') }}"
                                    >

                                    @error('email')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="subject">
                                        {{ trans('contact::attributes.subject') }}<span>*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="subject"
                                        value="{{ old('subject') }}"
                                        id="subject"
                                        class="form-control"
                                        placeholder="{{ trans('storefront::contact.subject_placeholder') }}"
                                    >

                                    @error('subject')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group contact-form__full">
                                    <label for="message">
                                        {{ trans('contact::attributes.message') }}<span>*</span>
                                    </label>

                                    <textarea
                                        rows="6"
                                        name="message"
                                        id="message"
                                        class="form-control"
                                        placeholder="{{ trans('storefront::contact.message_placeholder') }}"
                                    >{{ old('message') }}</textarea>

                                    @error('message')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="contact-form__full">
                                    @include('storefront::public.partials.google_recaptcha')
                                </div>

                                <div class="contact-form__actions contact-form__full">
                                    <button type="submit" class="btn btn-lg btn-primary contact-form__submit" data-loading>
                                        <i class="las la-paper-plane" aria-hidden="true"></i>
                                        {{ trans('storefront::contact.send_message') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/contact/main.scss',
    ])
@endpush

@push('scripts')
    @include('storefront::public.partials.google_recaptcha_script')
@endpush
