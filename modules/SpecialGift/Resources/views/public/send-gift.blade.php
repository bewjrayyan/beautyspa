@extends('storefront::public.layout')

@section('title', trans('specialgift::messages.page_title'))

@section('content')
    <section class="send-gift-page">
        <div class="container">
            <div class="send-gift-card">
                <p class="send-gift-badge">SPECIALGIFT</p>
                <h1 class="send-gift-title">{{ trans('specialgift::messages.page_title') }}</h1>
                <p class="send-gift-lead">{{ trans('specialgift::messages.page_lead') }}</p>

                @include('storefront::public.partials.notification')

                <div class="send-gift-alert send-gift-alert--error" id="send-gift-error" hidden></div>
                <div class="send-gift-alert send-gift-alert--success" id="send-gift-success" hidden></div>

                <form
                    method="POST"
                    action="{{ route('specialgift.send.store') }}"
                    class="send-gift-form"
                    id="send-gift-form"
                >
                    @csrf
                    @honeypot

                    <div class="form-group">
                        <label for="recipient_name">{{ trans('specialgift::messages.recipient_name') }} <span>*</span></label>
                        <input
                            type="text"
                            name="recipient_name"
                            id="recipient_name"
                            class="form-control"
                            value="{{ old('recipient_name') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="order_number">{{ trans('specialgift::messages.order_number') }} <span>*</span></label>
                        <input
                            type="text"
                            name="order_number"
                            id="order_number"
                            class="form-control"
                            value="{{ old('order_number') }}"
                            placeholder="#123"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="whatsapp_number">{{ trans('specialgift::messages.whatsapp_number') }} <span>*</span></label>
                        <input
                            type="tel"
                            name="whatsapp_number"
                            id="whatsapp_number"
                            class="form-control"
                            value="{{ old('whatsapp_number') }}"
                            placeholder="60123456789"
                            required
                        >
                        <p class="help-block">{{ trans('specialgift::messages.whatsapp_help') }}</p>
                    </div>

                    <div class="form-group">
                        <label for="sender_name">{{ trans('specialgift::messages.sender_name') }}</label>
                        <input
                            type="text"
                            name="sender_name"
                            id="sender_name"
                            class="form-control"
                            value="{{ old('sender_name') }}"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg send-gift-submit" id="send-gift-submit">
                        <span class="send-gift-submit-label">{{ trans('specialgift::messages.submit') }}</span>
                        <span class="send-gift-submit-loading" hidden>{{ trans('specialgift::messages.sending') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('globals')
    <style>
        .send-gift-page { padding: 48px 0 64px; }
        .send-gift-card {
            max-width: 560px;
            margin: 0 auto;
            padding: 32px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        }
        .send-gift-badge {
            display: inline-block;
            margin: 0 0 12px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: #be185d;
            background: #fce7f3;
            border-radius: 6px;
        }
        .send-gift-title { margin: 0 0 8px; font-size: 28px; font-weight: 700; }
        .send-gift-lead { margin: 0 0 24px; color: #6b7280; }
        .send-gift-form .form-group { margin-bottom: 18px; }
        .send-gift-form label span { color: #e11d48; }
        .send-gift-submit { width: 100%; margin-top: 8px; }
        .send-gift-submit:disabled { opacity: 0.7; cursor: wait; }
        .send-gift-alert {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 14px;
        }
        .send-gift-alert--error { background: #fef2f2; color: #b91c1c; }
        .send-gift-alert--success { background: #ecfdf5; color: #047857; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('send-gift-form');
            if (!form) return;

            const submitBtn = document.getElementById('send-gift-submit');
            const label = submitBtn.querySelector('.send-gift-submit-label');
            const loading = submitBtn.querySelector('.send-gift-submit-loading');
            const errorBox = document.getElementById('send-gift-error');
            const successBox = document.getElementById('send-gift-success');

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                errorBox.hidden = true;
                successBox.hidden = true;
                submitBtn.disabled = true;
                label.hidden = true;
                loading.hidden = false;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (response.ok && data.success) {
                        successBox.textContent = data.message || '{{ trans('specialgift::messages.sent_success') }}';
                        successBox.hidden = false;
                        form.reset();
                    } else {
                        errorBox.textContent = data.message || '{{ trans('specialgift::messages.send_failed') }}';
                        errorBox.hidden = false;
                    }
                } catch (e) {
                    errorBox.textContent = '{{ trans('specialgift::messages.send_failed') }}';
                    errorBox.hidden = false;
                } finally {
                    submitBtn.disabled = false;
                    label.hidden = false;
                    loading.hidden = true;
                }
            });
        });
    </script>
@endpush
