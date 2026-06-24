@extends('storefront::public.layout')

@section('title', trans('specialgift::messages.page_title'))

@section('body_class', 'page-send-gift')

@section('content')
    <section class="sg-page" aria-labelledby="sg-page-title">
        <div class="sg-page__bg" aria-hidden="true">
            <span class="sg-page__orb sg-page__orb--one"></span>
            <span class="sg-page__orb sg-page__orb--two"></span>
            <span class="sg-page__orb sg-page__orb--three"></span>
            <span class="sg-page__sparkle sg-page__sparkle--1">✦</span>
            <span class="sg-page__sparkle sg-page__sparkle--2">♥</span>
            <span class="sg-page__sparkle sg-page__sparkle--3">✦</span>
        </div>

        <div class="container sg-page__container">
            <header class="sg-hero">
                <p class="sg-hero__eyebrow">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M12 22V12M12 12 7 7.5 12 3l5 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7 7.5h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    {{ trans('specialgift::messages.page_tagline') }}
                </p>
                <h1 class="sg-hero__title" id="sg-page-title">{{ trans('specialgift::messages.page_title') }}</h1>
                <p class="sg-hero__lead">{{ trans('specialgift::messages.page_lead') }}</p>

                <ol class="sg-steps">
                    <li class="sg-steps__item">
                        <span class="sg-steps__icon" aria-hidden="true">1</span>
                        <span>{{ trans('specialgift::messages.step_order') }}</span>
                    </li>
                    <li class="sg-steps__item">
                        <span class="sg-steps__icon" aria-hidden="true">2</span>
                        <span>{{ trans('specialgift::messages.step_details') }}</span>
                    </li>
                    <li class="sg-steps__item">
                        <span class="sg-steps__icon" aria-hidden="true">3</span>
                        <span>{{ trans('specialgift::messages.step_send') }}</span>
                    </li>
                </ol>
            </header>

            <div class="sg-layout">
                <aside class="sg-preview" aria-label="{{ trans('specialgift::messages.preview_label') }}">
                    <div class="sg-preview__card">
                        <div class="sg-preview__ribbon" aria-hidden="true"></div>
                        <p class="sg-preview__label">{{ trans('specialgift::messages.preview_label') }}</p>

                        <div class="sg-preview__frame">
                            <img
                                src="{{ $voucherPreviewUrl }}"
                                alt=""
                                class="sg-preview__image"
                                width="600"
                                height="315"
                                loading="eager"
                            >
                            <div class="sg-preview__overlay">
                                <p class="sg-preview__name" id="sg-preview-name">{{ old('recipient_name', trans('specialgift::messages.preview_sample_name')) }}</p>
                                <p class="sg-preview__order" id="sg-preview-order">{{ trans('specialgift::messages.preview_sample_order') }}</p>
                            </div>
                        </div>

                        <p class="sg-preview__trust">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ trans('specialgift::messages.trust_note') }}
                        </p>
                    </div>
                </aside>

                <div class="sg-panel">
                    @include('storefront::public.auth.partials.notification')

                    <div class="sg-alert sg-alert--error" id="send-gift-error" role="alert" hidden></div>
                    <div class="sg-alert sg-alert--success" id="send-gift-success" role="status" hidden>
                        <span class="sg-alert__icon" aria-hidden="true">♥</span>
                        <span class="sg-alert__text"></span>
                    </div>

                    <div class="sg-form-card">
                        <h2 class="sg-form-card__title">{{ trans('specialgift::messages.form_title') }}</h2>

                        <form
                            method="POST"
                            action="{{ route('specialgift.send.store') }}"
                            class="sg-form"
                            id="send-gift-form"
                            novalidate
                        >
                            @csrf
                            @honeypot

                            <div class="sg-field">
                                <label class="sg-field__label" for="recipient_name">
                                    {{ trans('specialgift::messages.recipient_name') }}
                                    <span class="sg-field__required" aria-hidden="true">*</span>
                                </label>
                                <div class="sg-field__control">
                                    <span class="sg-field__icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 21a8 8 0 1 0-16 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.7"/></svg>
                                    </span>
                                    <input
                                        type="text"
                                        name="recipient_name"
                                        id="recipient_name"
                                        class="sg-input"
                                        value="{{ old('recipient_name') }}"
                                        placeholder="{{ trans('specialgift::messages.recipient_placeholder') }}"
                                        required
                                        autocomplete="name"
                                    >
                                </div>
                            </div>

                            <div class="sg-field">
                                <label class="sg-field__label" for="order_number">
                                    {{ trans('specialgift::messages.order_number') }}
                                    <span class="sg-field__required" aria-hidden="true">*</span>
                                </label>
                                <div class="sg-field__control">
                                    <span class="sg-field__icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="1.7"/></svg>
                                    </span>
                                    <input
                                        type="text"
                                        name="order_number"
                                        id="order_number"
                                        class="sg-input"
                                        value="{{ old('order_number') }}"
                                        placeholder="#123"
                                        required
                                        inputmode="numeric"
                                    >
                                </div>
                            </div>

                            <div class="sg-field">
                                <label class="sg-field__label" for="whatsapp_number">
                                    {{ trans('specialgift::messages.whatsapp_number') }}
                                    <span class="sg-field__required" aria-hidden="true">*</span>
                                </label>
                                <div class="sg-field__control">
                                    <span class="sg-field__icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8 8 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8 8 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <input
                                        type="tel"
                                        name="whatsapp_number"
                                        id="whatsapp_number"
                                        class="sg-input"
                                        value="{{ old('whatsapp_number') }}"
                                        placeholder="60123456789"
                                        required
                                        inputmode="tel"
                                        autocomplete="tel"
                                    >
                                </div>
                                <p class="sg-field__hint">{{ trans('specialgift::messages.whatsapp_help') }}</p>
                            </div>

                            <div class="sg-field">
                                <label class="sg-field__label" for="sender_name">{{ trans('specialgift::messages.sender_name') }}</label>
                                <div class="sg-field__control">
                                    <span class="sg-field__icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 4h16v16H4z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <input
                                        type="text"
                                        name="sender_name"
                                        id="sender_name"
                                        class="sg-input"
                                        value="{{ old('sender_name') }}"
                                        placeholder="{{ trans('specialgift::messages.sender_placeholder') }}"
                                        autocomplete="name"
                                    >
                                </div>
                            </div>

                            <button type="submit" class="sg-submit" id="send-gift-submit">
                                <span class="sg-submit__shine" aria-hidden="true"></span>
                                <span class="sg-submit__content">
                                    <svg class="sg-submit__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M12 22V12M12 12 7 7.5 12 3l5 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="send-gift-submit-label">{{ trans('specialgift::messages.submit') }}</span>
                                    <span class="send-gift-submit-loading" hidden>{{ trans('specialgift::messages.sending') }}</span>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@include('specialgift::public.partials.send-gift-styles')
@include('specialgift::public.partials.send-gift-scripts')
