<div
    class="whatsapp-otp-modal"
    x-data="whatsappOtpLogin"
    data-send-url="/login/whatsapp/send-otp"
    data-verify-url="/login/whatsapp/verify-otp"
    data-invalid-phone-message="{{ e(trans('core::validation.phone')) }}"
    x-show="isOpen"
    x-cloak
    @keydown.escape.window="closeModal()"
>
    <div class="whatsapp-otp-modal-backdrop" @click="closeModal()"></div>

    <div class="whatsapp-otp-modal-dialog" @click.stop>
        <button type="button" class="whatsapp-otp-modal-close" @click="closeModal()" aria-label="Close">
            &times;
        </button>

        <h3>{{ trans('user::auth.whatsapp_otp_title') }}</h3>

        <p class="text-red" x-show="error" x-text="error"></p>
        <p class="text-green" x-show="success" x-text="success"></p>

        <template x-if="step === 'phone'">
            <div>
                <label class="input-label" for="whatsapp-otp-phone">
                    {{ trans('user::auth.phone') }} <span>*</span>
                </label>
                @include('storefront::public.partials.phone_input', [
                    'name' => 'whatsapp_otp_phone',
                    'id' => 'whatsapp-otp-phone',
                    'placeholder' => trans('user::auth.whatsapp_otp_phone_hint'),
                    'extraAttributes' => '@phone:change="phone = $event.detail.number"',
                ])
                <button
                    type="button"
                    class="btn btn-primary"
                    style="margin-top: 16px; width: 100%;"
                    :class="loading ? 'btn-loading' : ''"
                    :disabled="loading"
                    @click="sendOtp()"
                >
                    {{ trans('user::auth.whatsapp_otp_send') }}
                </button>
            </div>
        </template>

        <template x-if="step === 'otp'">
            <div>
                @include('storefront::public.partials.otp_digit_input', [
                    'idPrefix' => 'whatsapp-otp',
                    'model' => 'otp',
                    'showPhone' => true,
                ])
                <button
                    type="button"
                    class="btn btn-primary"
                    style="margin-top: 16px; width: 100%;"
                    :class="loading ? 'btn-loading' : ''"
                    :disabled="loading"
                    @click="verifyOtp()"
                >
                    {{ trans('user::auth.whatsapp_otp_verify') }}
                </button>
                <div style="margin-top: 12px; display: flex; justify-content: space-between; gap: 8px;">
                    <button type="button" class="btn btn-default btn-sm" @click="step = 'phone'; otp = ''; error = ''">
                        {{ trans('user::auth.whatsapp_otp_back') }}
                    </button>
                    <button type="button" class="btn btn-default btn-sm" :disabled="loading" @click="sendOtp()">
                        {{ trans('user::auth.whatsapp_otp_resend') }}
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
