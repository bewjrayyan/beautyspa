<?php

namespace Modules\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPhone implements ValidationRule
{
    /**
     * Validate E.164 international phone numbers (e.g. +60123456789).
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $normalized = preg_replace('/\s+/', '', $value);

        if (! preg_match('/^\+[1-9]\d{6,14}$/', $normalized)) {
            $fail(__('core::validation.phone'));
        }
    }
}
