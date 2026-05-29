@extends('storefront::public.layout')

@section('title', trans('treatmentreservation::public.title'))

@section('breadcrumb')
    <li class="active">{{ trans('treatmentreservation::public.title') }}</li>
@endsection

@section('content')
    <section class="custom-page-wrap my-appointments-wrap clearfix">
        <div class="container">
            <header class="my-appointments-hero">
                <div class="my-appointments-hero__icon" aria-hidden="true">
                    <i class="las la-calendar-check"></i>
                </div>
                <h1>{{ trans('treatmentreservation::public.title') }}</h1>
                <p class="my-appointments-hero__subtitle">{{ trans('treatmentreservation::public.subtitle') }}</p>
            </header>

            <div class="my-appointments-layout {{ $verifiedPhone ? 'my-appointments-layout--verified' : '' }}">
                @unless ($verifiedPhone)
                    <aside class="my-appointments-steps" aria-label="{{ trans('treatmentreservation::public.how_it_works') }}">
                        <h2>{{ trans('treatmentreservation::public.how_it_works') }}</h2>

                        <div class="my-appointments-step">
                            <span class="my-appointments-step__number">1</span>
                            <div>
                                <span class="my-appointments-step__title">{{ trans('treatmentreservation::public.step_1_title') }}</span>
                                <span class="my-appointments-step__text">{{ trans('treatmentreservation::public.step_1_text') }}</span>
                            </div>
                        </div>

                        <div class="my-appointments-step">
                            <span class="my-appointments-step__number">2</span>
                            <div>
                                <span class="my-appointments-step__title">{{ trans('treatmentreservation::public.step_2_title') }}</span>
                                <span class="my-appointments-step__text">{{ trans('treatmentreservation::public.step_2_text') }}</span>
                            </div>
                        </div>

                        <div class="my-appointments-step">
                            <span class="my-appointments-step__number">3</span>
                            <div>
                                <span class="my-appointments-step__title">{{ trans('treatmentreservation::public.step_3_title') }}</span>
                                <span class="my-appointments-step__text">{{ trans('treatmentreservation::public.step_3_text') }}</span>
                            </div>
                        </div>
                    </aside>
                @endunless

                <div class="my-appointments-panel">
                    <p class="alert alert-danger my-appointments-alert" id="booking-page-error" style="display:none;" role="alert"></p>

                    @if ($verifiedPhone)
                        <div class="my-appointments-toolbar">
                            <div class="my-appointments-toolbar__phone">
                                <i class="lab la-whatsapp" aria-hidden="true"></i>
                                <span>
                                    {{ trans('treatmentreservation::public.verified_as') }}
                                    <strong>{{ $verifiedPhone }}</strong>
                                </span>
                            </div>

                            <form method="POST" action="{{ route('treatment_reservations.booking.logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-default btn-sm">
                                    <i class="las la-sign-out-alt"></i>
                                    {{ trans('treatmentreservation::public.logout') }}
                                </button>
                            </form>
                        </div>

                        @if ($bookings->isEmpty())
                            <div class="my-appointments-empty">
                                <div class="my-appointments-empty__icon" aria-hidden="true">
                                    <i class="las la-calendar-times"></i>
                                </div>
                                <h3>{{ trans('treatmentreservation::public.empty_title') }}</h3>
                                <p>{{ trans('treatmentreservation::public.empty_text') }}</p>
                                <a href="{{ route('products.index') }}" class="btn btn-primary">
                                    {{ trans('treatmentreservation::public.browse_treatments') }}
                                </a>
                            </div>
                        @else
                            <div class="my-appointments-list">
                                @foreach ($bookings as $booking)
                                    @php
                                        $statusKey = 'treatmentreservation::public.statuses.' . $booking->status;
                                        $statusLabel = trans()->has($statusKey)
                                            ? trans($statusKey)
                                            : ucfirst(str_replace('_', ' ', $booking->status));
                                    @endphp

                                    <article class="my-appointments-card" data-booking-id="{{ $booking->id }}">
                                        <div class="my-appointments-card__top">
                                            <h3 class="my-appointments-card__title">{{ $booking->product?->name }}</h3>
                                            <span class="my-appointments-status my-appointments-status--{{ $booking->status }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </div>

                                        <div class="my-appointments-card__meta">
                                            <div class="my-appointments-card__meta-item">
                                                <i class="las la-calendar" aria-hidden="true"></i>
                                                <div>
                                                    <span>{{ trans('treatmentreservation::public.date') }}</span>
                                                    <strong>{{ $booking->appointment_date?->format('d M Y') }}</strong>
                                                </div>
                                            </div>
                                            <div class="my-appointments-card__meta-item">
                                                <i class="las la-clock" aria-hidden="true"></i>
                                                <div>
                                                    <span>{{ trans('treatmentreservation::public.time') }}</span>
                                                    <strong>{{ $booking->appointment_time }}</strong>
                                                </div>
                                            </div>
                                            <div class="my-appointments-card__meta-item">
                                                <i class="las la-user" aria-hidden="true"></i>
                                                <div>
                                                    <span>{{ trans('treatmentreservation::public.beautician') }}</span>
                                                    <strong>{{ $booking->beautician?->name ?? '—' }}</strong>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="my-appointments-card__actions">
                                            <button type="button" class="btn btn-default btn-sm js-reschedule-toggle">
                                                <i class="las la-edit"></i>
                                                {{ trans('treatmentreservation::public.reschedule') }}
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm js-cancel-booking">
                                                <i class="las la-times-circle"></i>
                                                {{ trans('treatmentreservation::public.cancel') }}
                                            </button>
                                        </div>

                                        <form
                                            class="my-appointments-card__reschedule hide js-reschedule-form"
                                            data-slots-url="{{ route('treatment_reservations.booking.slots', ['id' => $booking->id]) }}"
                                        >
                                            <div class="row-fields">
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('treatmentreservation::public.new_date') }}</label>
                                                    <input
                                                        type="date"
                                                        name="appointment_date"
                                                        class="form-control js-reschedule-date"
                                                        required
                                                        min="{{ today()->toDateString() }}"
                                                    >
                                                </div>
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('treatmentreservation::public.new_time') }}</label>
                                                    <select name="appointment_time" class="form-control js-slot-select" required disabled>
                                                        <option value="">{{ trans('treatmentreservation::public.loading_slots') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                {{ trans('treatmentreservation::public.reschedule') }}
                                            </button>
                                        </form>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div id="booking-otp-app" class="my-appointments-otp">
                            <div class="my-appointments-otp__header">
                                <div class="my-appointments-otp__whatsapp-badge">
                                    <i class="lab la-whatsapp" aria-hidden="true"></i>
                                    {{ trans('treatmentreservation::public.whatsapp_secure') }}
                                </div>
                                <h2>{{ trans('treatmentreservation::public.otp_title') }}</h2>
                                <p>{{ trans('treatmentreservation::public.otp_hint') }}</p>
                            </div>

                            <div class="my-appointments-otp__progress" role="tablist" aria-label="{{ trans('treatmentreservation::public.otp_title') }}">
                                <span class="my-appointments-otp__progress-step is-active" id="booking-progress-phone" role="tab">
                                    {{ trans('treatmentreservation::public.otp_step_phone') }}
                                </span>
                                <span class="my-appointments-otp__progress-step" id="booking-progress-code" role="tab">
                                    {{ trans('treatmentreservation::public.otp_step_code') }}
                                </span>
                            </div>

                            <p class="alert alert-danger my-appointments-alert" id="booking-otp-error" style="display:none;" role="alert"></p>

                            <div id="booking-otp-phone-step">
                                @include('storefront::public.partials.phone_input', [
                                    'name' => 'phone',
                                    'id' => 'booking-otp-phone',
                                    'placeholder' => trans('user::auth.whatsapp_otp_phone_hint'),
                                ])
                                <button type="button" class="btn btn-primary" id="booking-otp-send">
                                    <i class="lab la-whatsapp"></i>
                                    {{ trans('user::auth.whatsapp_otp_send') }}
                                </button>
                            </div>

                            <div id="booking-otp-code-step" style="display:none;">
                                @include('storefront::public.partials.otp_digit_input', [
                                    'idPrefix' => 'booking-otp',
                                    'useAlpine' => false,
                                    'hiddenInputId' => 'booking-otp-code',
                                    'showPhone' => true,
                                    'phoneDisplayId' => 'booking-otp-phone-display',
                                ])

                                <button type="button" class="btn btn-primary" id="booking-otp-verify">
                                    {{ trans('user::auth.whatsapp_otp_verify') }}
                                </button>

                                <button type="button" class="my-appointments-otp__back" id="booking-otp-back">
                                    <i class="las la-arrow-left"></i>
                                    {{ trans('treatmentreservation::public.otp_change_phone') }}
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    @vite('modules/Storefront/Resources/assets/public/sass/pages/custom-page/main.scss')
@endpush

@push('scripts')
    <script>
        (function () {
            const csrf = window.FleetCart?.csrfToken || '';
            const endpoints = {
                sendOtp: FleetCart.url('/my-appointments/send-otp'),
                verifyOtp: FleetCart.url('/my-appointments/verify-otp'),
                cancel: (id) => FleetCart.url(`/my-appointments/${id}/cancel`),
                reschedule: (id) => FleetCart.url(`/my-appointments/${id}/reschedule`),
            };
            let phone = '';

            const jsonRequest = (url, options = {}) => fetch(url, {
                credentials: 'same-origin',
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {}),
                },
            });

            const errorEl = document.getElementById('booking-otp-error');
            const pageErrorEl = document.getElementById('booking-page-error');
            const progressPhone = document.getElementById('booking-progress-phone');
            const progressCode = document.getElementById('booking-progress-code');
            const phoneStep = document.getElementById('booking-otp-phone-step');
            const codeStep = document.getElementById('booking-otp-code-step');

            const setOtpStep = (step) => {
                if (!phoneStep || !codeStep) {
                    return;
                }

                const isCode = step === 'code';

                phoneStep.style.display = isCode ? 'none' : 'block';
                codeStep.style.display = isCode ? 'block' : 'none';

                progressPhone?.classList.toggle('is-active', !isCode);
                progressPhone?.classList.toggle('is-done', isCode);
                progressCode?.classList.toggle('is-active', isCode);
            };

            const showError = (message) => {
                if (!errorEl) {
                    return;
                }

                errorEl.textContent = message;
                errorEl.style.display = message ? 'block' : 'none';
            };

            const showPageError = (message) => {
                if (!pageErrorEl) {
                    return;
                }

                pageErrorEl.textContent = message;
                pageErrorEl.style.display = message ? 'block' : 'none';
            };

            const setLoading = (button, loading) => {
                if (!button) {
                    return;
                }

                button.disabled = loading;
                button.classList.toggle('btn-loading', loading);
            };

            let bookingOtpApi = null;

            const phoneInput = document.getElementById('booking-otp-phone');
            const sendBtn = document.getElementById('booking-otp-send');
            const verifyBtn = document.getElementById('booking-otp-verify');
            const backBtn = document.getElementById('booking-otp-back');

            const resolvePhone = () => {
                if (phoneInput?._iti) {
                    return phoneInput._iti.getNumber() || phoneInput.dataset.fullNumber || phoneInput.value || '';
                }

                return phoneInput?.dataset.fullNumber || phoneInput?.value || '';
            };

            const handleActionError = async (response) => {
                let message = @json(trans('treatmentreservation::public.action_failed'));

                try {
                    const data = await response.json();
                    message = data.message || message;
                } catch (error) {
                    // Keep default message.
                }

                showPageError(message);
            };

            sendBtn?.addEventListener('click', async () => {
                showError('');
                phone = resolvePhone();
                setLoading(sendBtn, true);

                try {
                    const response = await jsonRequest(endpoints.sendOtp, {
                        method: 'POST',
                        body: JSON.stringify({ phone }),
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Error');
                    }

                    document.getElementById('booking-otp-phone-display').textContent = phone;
                    setOtpStep('code');

                    const otpRoot = document.querySelector('#booking-otp-code-step [data-otp-digit-input]');

                    if (bookingOtpApi) {
                        bookingOtpApi.fill('');
                        bookingOtpApi.focus(0);
                    }
                } catch (error) {
                    showError(error.message);
                } finally {
                    setLoading(sendBtn, false);
                }
            });

            verifyBtn?.addEventListener('click', async () => {
                showError('');
                setLoading(verifyBtn, true);

                try {
                    const response = await jsonRequest(endpoints.verifyOtp, {
                        method: 'POST',
                        body: JSON.stringify({
                            phone,
                            otp: document.getElementById('booking-otp-code')?.value || '',
                        }),
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Error');
                    }

                    window.location.reload();
                } catch (error) {
                    showError(error.message);
                } finally {
                    setLoading(verifyBtn, false);
                }
            });

            backBtn?.addEventListener('click', () => {
                showError('');
                setOtpStep('phone');
                bookingOtpApi?.fill('');
            });

            const otpRoot = document.querySelector('#booking-otp-app [data-otp-digit-input]');

            if (otpRoot && window.initOtpDigitInput) {
                bookingOtpApi = window.initOtpDigitInput(otpRoot, {
                    hiddenInputId: 'booking-otp-code',
                    length: 6,
                });
                otpRoot.__otpApi = bookingOtpApi;
            }

            document.querySelectorAll('.js-cancel-booking').forEach((button) => {
                button.addEventListener('click', async () => {
                    if (!confirm(@json(trans('treatmentreservation::public.confirm_cancel')))) {
                        return;
                    }

                    const card = button.closest('[data-booking-id]');
                    const id = card?.dataset.bookingId;

                    setLoading(button, true);

                    const response = await jsonRequest(endpoints.cancel(id), {
                        method: 'PATCH',
                    });

                    if (response.ok) {
                        window.location.reload();
                        return;
                    }

                    setLoading(button, false);
                    await handleActionError(response);
                });
            });

            document.querySelectorAll('.js-reschedule-toggle').forEach((button) => {
                button.addEventListener('click', () => {
                    const form = button.closest('[data-booking-id]')?.querySelector('.js-reschedule-form');
                    form?.classList.toggle('hide');

                    if (form && !form.classList.contains('hide')) {
                        loadSlots(form);
                    }
                });
            });

            document.querySelectorAll('.js-reschedule-date').forEach((input) => {
                input.addEventListener('change', () => {
                    const form = input.closest('.js-reschedule-form');

                    if (form) {
                        loadSlots(form);
                    }
                });
            });

            async function loadSlots(form) {
                const date = form.querySelector('.js-reschedule-date')?.value;
                const select = form.querySelector('.js-slot-select');
                const slotsUrl = form.dataset.slotsUrl;

                if (!date || !select || !slotsUrl) {
                    return;
                }

                select.disabled = true;
                select.innerHTML = `<option value="">${@json(trans('treatmentreservation::public.loading_slots'))}</option>`;

                try {
                    const response = await fetch(`${slotsUrl}?date=${encodeURIComponent(date)}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || @json(trans('treatmentreservation::public.action_failed')));
                    }

                    const slots = data.slots || [];

                    if (slots.length === 0) {
                        select.innerHTML = `<option value="">${@json(trans('treatmentreservation::public.no_slots'))}</option>`;
                        return;
                    }

                    select.innerHTML = slots
                        .map((slot) => `<option value="${slot}">${slot}</option>`)
                        .join('');
                    select.disabled = false;
                } catch (error) {
                    showPageError(error.message);
                    select.innerHTML = `<option value="">${@json(trans('treatmentreservation::public.no_slots'))}</option>`;
                }
            }

            document.querySelectorAll('.js-reschedule-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const card = form.closest('[data-booking-id]');
                    const id = card?.dataset.bookingId;
                    const date = form.querySelector('[name="appointment_date"]')?.value;
                    const time = form.querySelector('[name="appointment_time"]')?.value;
                    const submitBtn = form.querySelector('[type="submit"]');

                    setLoading(submitBtn, true);

                    const response = await jsonRequest(endpoints.reschedule(id), {
                        method: 'PATCH',
                        body: JSON.stringify({
                            appointment_date: date,
                            appointment_time: time,
                        }),
                    });

                    if (response.ok) {
                        window.location.reload();
                        return;
                    }

                    setLoading(submitBtn, false);
                    await handleActionError(response);
                });
            });
        })();
    </script>
@endpush
