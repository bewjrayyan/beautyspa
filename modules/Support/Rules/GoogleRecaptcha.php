<?php

namespace Modules\Support\Rules;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\Support\GoogleRecaptchaSettings;

class GoogleRecaptcha implements ValidationRule
{
    public function __construct(
        private readonly ?string $action = null,
    ) {
    }


    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! GoogleRecaptchaSettings::enabled()) {
            return;
        }

        if (! is_string($value) || trim($value) === '') {
            $fail(trans('support::recaptcha.validation.failed_to_verify'));

            return;
        }

        $response = Http::timeout(10)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => GoogleRecaptchaSettings::secretKey(),
            'response' => $value,
        ]);

        if (! ($response->json('success') ?? false)) {
            $fail(trans('support::recaptcha.validation.failed_to_verify'));

            return;
        }

        if (! GoogleRecaptchaSettings::isV3()) {
            return;
        }

        $score = (float) ($response->json('score') ?? 0);

        if ($score < GoogleRecaptchaSettings::v3ScoreThreshold()) {
            $fail(trans('support::recaptcha.validation.low_score'));

            return;
        }

        if ($this->action !== null && $response->json('action') !== $this->action) {
            $fail(trans('support::recaptcha.validation.failed_to_verify'));
        }
    }
}
