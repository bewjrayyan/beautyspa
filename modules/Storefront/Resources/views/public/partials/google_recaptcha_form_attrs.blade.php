@if (\Modules\Support\GoogleRecaptchaSettings::isV3()) data-recaptcha-action="{{ $action ?? 'submit' }}" @endif
