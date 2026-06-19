@extends('storefront::public.account.layout')

@section('title', trans('treatmentreservation::public.title'))

@section('account_breadcrumb')
    <li class="active">{{ trans('treatmentreservation::public.title') }}</li>
@endsection

@section('panel')
    <div class="panel account-appointments-panel">
        <div class="panel-header d-none d-lg-flex">
            <h4>{{ trans('treatmentreservation::public.title') }}</h4>

            @if ($verifiedPhone)
                <div class="account-appointments-toolbar">
                    <span class="account-appointments-toolbar__phone">
                        <i class="lab la-whatsapp" aria-hidden="true"></i>
                        {{ trans('treatmentreservation::public.verified_as') }}
                        <strong>{{ $verifiedPhone }}</strong>
                    </span>

                    <form method="POST" action="{{ route('treatment_reservations.booking.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-default btn-sm">
                            <i class="las la-sign-out-alt"></i>
                            {{ trans('treatmentreservation::public.logout') }}
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if ($verifiedPhone)
            <div class="account-appointments-toolbar account-appointments-toolbar--mobile d-lg-none">
                <span class="account-appointments-toolbar__phone">
                    <i class="lab la-whatsapp" aria-hidden="true"></i>
                    {{ trans('treatmentreservation::public.verified_as') }}
                    <strong>{{ $verifiedPhone }}</strong>
                </span>

                <form method="POST" action="{{ route('treatment_reservations.booking.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-default btn-sm">
                        <i class="las la-sign-out-alt"></i>
                        {{ trans('treatmentreservation::public.logout') }}
                    </button>
                </form>
            </div>
        @endif

        <div class="panel-body">
            <p class="alert alert-danger account-appointments-alert" id="booking-page-error" style="display:none;" role="alert"></p>

            @unless ($verifiedPhone)
                @include('treatmentreservation::public.booking.partials.otp_panel')
            @elseif ($bookings->isEmpty())
                <div class="empty-message">
                    <h3>{{ trans('treatmentreservation::public.empty_title') }}</h3>
                    <p>{{ trans('treatmentreservation::public.empty_text') }}</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm">
                        {{ trans('treatmentreservation::public.browse_treatments') }}
                    </a>
                </div>
            @else
                @include('treatmentreservation::public.booking.partials.appointments_cards', ['bookings' => $bookings])
                @include('treatmentreservation::public.booking.partials.appointments_table', ['bookings' => $bookings])
            @endif
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/appointments/main.scss',
    ])
@endpush

@push('scripts')
    <script>
        (function () {
            const csrf = window.AestheticCart?.csrfToken || '';
            const endpoints = {
                sendOtp: AestheticCart.url('/my-appointments/send-otp'),
                verifyOtp: AestheticCart.url('/my-appointments/verify-otp'),
                cancel: (id) => AestheticCart.url(`/my-appointments/${id}/cancel`),
                reschedule: (id) => AestheticCart.url(`/my-appointments/${id}/reschedule`),
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
                    bookingOtpApi?.fill('');
                    bookingOtpApi?.focus(0);
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

            const findBookingRoot = (button) => {
                const card = button.closest('[data-booking-id]');

                if (card) {
                    return card;
                }

                const row = button.closest('.my-appointments-table__row');

                return row || null;
            };

            const findBookingId = (button) => findBookingRoot(button)?.dataset.bookingId;

            document.querySelectorAll('.js-cancel-booking').forEach((button) => {
                button.addEventListener('click', async () => {
                    if (!confirm(@json(trans('treatmentreservation::public.confirm_cancel')))) {
                        return;
                    }

                    const id = findBookingId(button);

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
                    const card = button.closest('.account-appointment-card');

                    if (card) {
                        const form = card.querySelector('.js-reschedule-form');
                        form?.classList.toggle('hide');

                        if (form && !form.classList.contains('hide')) {
                            loadSlots(form);
                        }

                        return;
                    }

                    const row = button.closest('.my-appointments-table__row');
                    const id = row?.dataset.bookingId;
                    const expandRow = document.querySelector(`.my-appointments-table__expand[data-booking-id="${id}"]`);
                    const form = expandRow?.querySelector('.js-reschedule-form');

                    expandRow?.classList.toggle('hide');

                    if (form && expandRow && !expandRow.classList.contains('hide')) {
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
                    const row = form.closest('.my-appointments-table__expand');
                    const id = card?.dataset.bookingId || row?.dataset.bookingId;
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
