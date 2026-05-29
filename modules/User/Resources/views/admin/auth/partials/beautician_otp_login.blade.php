<div class="admin-beautician-otp__tabs">
    <button type="button" class="admin-beautician-otp__tab" :class="{ 'is-active': mode === 'email' }" @click="mode = 'email'">
        {{ trans('treatmentreservation::admin.portal.otp_email_tab') }}
    </button>
    <button type="button" class="admin-beautician-otp__tab" :class="{ 'is-active': mode === 'whatsapp' }" @click="mode = 'whatsapp'; initPhoneInput()">
        <i class="fa fa-whatsapp"></i> {{ trans('treatmentreservation::admin.portal.otp_tab') }}
    </button>
</div>

<p class="admin-beautician-otp__hint" x-show="mode === 'whatsapp'" x-cloak>
    {{ trans('treatmentreservation::admin.portal.otp_hint') }}
</p>

<div x-show="mode === 'whatsapp'" x-cloak class="admin-beautician-otp__panel">
    <template x-if="error">
        <div class="alert alert-danger" x-text="error"></div>
    </template>

    <div x-show="step === 'phone'">
        <div class="form-group">
            <label class="input-label" for="beautician-otp-phone">
                {{ trans('user::auth.phone') }} <span>*</span>
            </label>
            @include('storefront::public.partials.phone_input', [
                'name' => 'beautician_otp_phone',
                'id' => 'beautician-otp-phone',
                'placeholder' => trans('user::auth.whatsapp_otp_phone_hint'),
                'extraAttributes' => '@phone:change="phone = $event.detail.number; phoneValid = $event.detail.valid"',
            ])
        </div>

        <button type="button" class="btn btn-primary btn-block" :disabled="loading || !phoneValid" @click="sendOtp()">
            <span x-show="!loading">{{ trans('user::auth.whatsapp_otp_send') }}</span>
            <span x-show="loading">{{ trans('treatmentreservation::admin.calendar.loading') }}</span>
        </button>
    </div>

    <div x-show="step === 'otp'">
        <p class="help-block">{{ trans('user::auth.whatsapp_otp_phone_hint') }}: <strong x-text="phone"></strong></p>

        @include('storefront::public.partials.otp_digit_input', [
            'idPrefix' => 'beautician-otp',
            'model' => 'otp',
            'showPhone' => true,
        ])

        <button type="button" class="btn btn-primary btn-block" :disabled="loading || otp.length < 6" @click="verifyOtp()">
            <span x-show="!loading">{{ trans('user::auth.whatsapp_otp_verify') }}</span>
            <span x-show="loading">{{ trans('treatmentreservation::admin.calendar.loading') }}</span>
        </button>

        <div class="admin-beautician-otp__actions">
            <button type="button" class="btn admin-beautician-otp__action-btn" @click="step = 'phone'; otp = ''">
                {{ trans('user::auth.whatsapp_otp_back') }}
            </button>
            <button type="button" class="btn admin-beautician-otp__action-btn admin-beautician-otp__action-btn--primary" :disabled="loading" @click="sendOtp()">
                {{ trans('user::auth.whatsapp_otp_resend') }}
            </button>
        </div>
    </div>
</div>
