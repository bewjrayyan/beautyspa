@if (\Modules\Support\GoogleRecaptchaSettings::enabled())
    @if (\Modules\Support\GoogleRecaptchaSettings::isV3())
        <script src="https://www.google.com/recaptcha/api.js?render={{ \Modules\Support\GoogleRecaptchaSettings::siteKey() }}"></script>
        <script>
            window.AestheticCart = window.AestheticCart || {};
            AestheticCart.recaptchaV3SiteKey = @json(\Modules\Support\GoogleRecaptchaSettings::siteKey());
            AestheticCart.recaptchaV3Enabled = true;

            window.resolveRecaptchaToken = async function (action) {
                if (!AestheticCart.recaptchaV3SiteKey || !window.grecaptcha?.execute) {
                    return '';
                }

                return await grecaptcha.execute(AestheticCart.recaptchaV3SiteKey, {
                    action: action || 'submit',
                });
            };

            document.addEventListener('submit', function (event) {
                const form = event.target;

                if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-recaptcha-action')) {
                    return;
                }

                if (!AestheticCart.recaptchaV3Enabled) {
                    return;
                }

                if (form.dataset.recaptchaSubmitting === '1') {
                    form.dataset.recaptchaSubmitting = '0';

                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                const action = form.getAttribute('data-recaptcha-action') || 'submit';

                grecaptcha.execute(AestheticCart.recaptchaV3SiteKey, { action }).then(function (token) {
                    let input = form.querySelector('[name="g-recaptcha-response"]');

                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'g-recaptcha-response';
                        form.appendChild(input);
                    }

                    input.value = token;
                    form.dataset.recaptchaSubmitting = '1';
                    form.requestSubmit();
                });
            }, true);
        </script>
    @else
        <script async src="https://www.google.com/recaptcha/api.js"></script>
    @endif
@endif
