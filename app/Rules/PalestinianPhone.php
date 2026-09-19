<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class PalestinianPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail(__('site.form.errors.phone'));

            return;
        }

        $normalized = preg_replace('/[\s\-().]/', '', $value) ?? '';

        if (! preg_match('/^(?:(?:00|\+)?970|0)?5[69]\d{7}$/', $normalized)) {
            $fail(__('site.form.errors.phone'));
        }
    }
}
