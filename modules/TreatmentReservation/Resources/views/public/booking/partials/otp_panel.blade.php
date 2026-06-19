<div class="account-appointments-otp">
    <p class="account-appointments-otp__lead">{{ trans('treatmentreservation::public.otp_hint') }}</p>

    <div class="account-appointments-otp__steps">
        <span>{{ trans('treatmentreservation::public.step_1_title') }}</span>
        <span>{{ trans('treatmentreservation::public.step_2_title') }}</span>
        <span>{{ trans('treatmentreservation::public.step_3_title') }}</span>
    </div>

    <div class="account-appointments-otp__badge">
        <i class="lab la-whatsapp" aria-hidden="true"></i>
        {{ trans('treatmentreservation::public.whatsapp_secure') }}
    </div>

    <div id="booking-otp-app" class="account-appointments-otp__form">
        <div class="account-appointments-otp__progress" role="tablist" aria-label="{{ trans('treatmentreservation::public.otp_title') }}">
            <span class="account-appointments-otp__progress-step is-active" id="booking-progress-phone" role="tab">
                {{ trans('treatmentreservation::public.otp_step_phone') }}
            </span>
            <span class="account-appointments-otp__progress-step" id="booking-progress-code" role="tab">
                {{ trans('treatmentreservation::public.otp_step_code') }}
            </span>
        </div>

        <p class="alert alert-danger account-appointments-alert" id="booking-otp-error" style="display:none;" role="alert"></p>

        <div id="booking-otp-phone-step">
            @include('storefront::public.partials.phone_input', [
                'name' => 'phone',
                'id' => 'booking-otp-phone',
                'placeholder' => trans('user::auth.whatsapp_otp_phone_hint'),
            ])
            <button type="button" class="btn btn-primary btn-block" id="booking-otp-send">
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

            <button type="button" class="btn btn-primary btn-block" id="booking-otp-verify">
                {{ trans('user::auth.whatsapp_otp_verify') }}
            </button>

            <button type="button" class="account-appointments-otp__back" id="booking-otp-back">
                <i class="las la-arrow-left"></i>
                {{ trans('treatmentreservation::public.otp_change_phone') }}
            </button>
        </div>
    </div>
</div>
