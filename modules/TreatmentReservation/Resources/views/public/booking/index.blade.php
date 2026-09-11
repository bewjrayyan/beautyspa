@extends('storefront::public.account.layout')

@section('title', trans('treatmentreservation::public.title'))

@section('account_breadcrumb')
    <li class="active">{{ trans('treatmentreservation::public.title') }}</li>
@endsection

@section('panel')
    <div class="panel account-appointments-panel">
        <div class="panel-header account-appointments-header">
            <div class="account-appointments-header__title">
                <span class="account-appointments-header__icon" aria-hidden="true">
                    <i class="las la-calendar-check"></i>
                </span>
                <div>
                    <h1>{{ trans('treatmentreservation::public.title') }}</h1>
                    <p>{{ trans('treatmentreservation::public.page_lead') }}</p>
                </div>
            </div>

            @if ($hasBookingAccess)
                <div class="account-appointments-toolbar">
                    @if ($usingAccountAccess)
                        <span class="account-appointments-toolbar__phone">
                            <i class="las la-lock" aria-hidden="true"></i>
                            {{ trans('treatmentreservation::public.account_access') }}
                        </span>
                    @else
                        <span class="account-appointments-toolbar__phone">
                            <i class="lab la-whatsapp" aria-hidden="true"></i>
                            {{ trans('treatmentreservation::public.verified_as') }}
                            <strong>{{ $verifiedPhone }}</strong>
                        </span>

                        <form method="POST" action="{{ route('treatment_reservations.booking.logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-default btn-sm">
                                <i class="las la-sign-out-alt" aria-hidden="true"></i>
                                {{ trans('treatmentreservation::public.logout') }}
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

        <div class="panel-body">
            <p class="alert alert-danger account-appointments-alert" id="booking-page-error" style="display:none;" role="alert"></p>

            @unless ($hasBookingAccess)
                @include('treatmentreservation::public.booking.partials.otp_panel')
            @elseif ($bookings->isEmpty())
                <div class="empty-message">
                    <span class="empty-message__icon" aria-hidden="true">
                        <i class="las la-calendar-times"></i>
                    </span>
                    <h3>{{ trans('treatmentreservation::public.empty_title') }}</h3>
                    <p>{{ trans('treatmentreservation::public.empty_text') }}</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm">
                        {{ trans('treatmentreservation::public.browse_treatments') }}
                    </a>
                </div>
            @else
                @include('treatmentreservation::public.booking.partials.appointments_cards', [
                    'bookingGroups' => $bookingGroups,
                    'bookingOrderCount' => $bookingGroups->count(),
                    'appointmentCount' => $bookings->count(),
                ])
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
    <script src="{{ asset('modules/lead/central/qrcode.js') }}?v={{ @filemtime(public_path('modules/lead/central/qrcode.js')) ?: time() }}"></script>
    <script>
        (function () {
            const csrf = window.AestheticCart?.csrfToken || '';
            const endpoints = {
                sendOtp: AestheticCart.url('/my-appointments/send-otp'),
                verifyOtp: AestheticCart.url('/my-appointments/verify-otp'),
                cancel: (id) => AestheticCart.url(`/my-appointments/${id}/cancel`),
            };
            let phone = '';

            document.querySelectorAll('[data-checkin-pass]').forEach((canvas) => {
                const payload = canvas.dataset.checkinPass || '';
                if (! payload || ! window.QRCentral) {
                    return;
                }

                try {
                    window.QRCentral.render(canvas, payload, { size: 200 });
                } catch (error) {
                    console.error('Could not render check-in pass QR', error);
                    canvas.hidden = true;
                }
            });

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

        })();
    </script>
@endpush
