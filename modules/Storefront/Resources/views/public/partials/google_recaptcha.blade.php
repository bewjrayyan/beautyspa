@if (\Modules\Support\GoogleRecaptchaSettings::enabled())
    @if (\Modules\Support\GoogleRecaptchaSettings::isV2())
        <div class="form-group captcha-field">
            <div class="g-recaptcha" data-sitekey="{{ \Modules\Support\GoogleRecaptchaSettings::siteKey() }}"></div>

            @error('g-recaptcha-response')
                <span class="help-block text-red">{{ $message }}</span>
            @enderror
        </div>
    @else
        <input type="hidden" name="g-recaptcha-response" value="">

        @error('g-recaptcha-response')
            <span class="help-block text-red">{{ $message }}</span>
        @enderror
    @endif
@endif
