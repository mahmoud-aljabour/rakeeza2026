<?php

declare(strict_types=1);

namespace App\Support;

final class TranslatesCatalog
{
    public static function text(string $key, string $fallback, ?string $storedEnglish = null): string
    {
        if (app()->getLocale() !== AppLocale::ENGLISH) {
            return $fallback;
        }

        if (is_string($storedEnglish) && $storedEnglish !== '') {
            return $storedEnglish;
        }

        $translated = trans($key, [], AppLocale::ENGLISH);

        return is_string($translated) && $translated !== $key ? $translated : $fallback;
    }
}
