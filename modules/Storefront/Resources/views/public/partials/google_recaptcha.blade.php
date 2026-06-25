@if (setting('google_recaptcha_enabled'))
    <div class="form-group captcha-field">
        <div class="g-recaptcha" data-sitekey="{{ setting('google_recaptcha_site_key') }}"></div>

        @error('g-recaptcha-response')
            <span class="help-block text-red">{{ $message }}</span>
        @enderror
    </div>
@endif
