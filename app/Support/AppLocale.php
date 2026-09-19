<?php

declare(strict_types=1);

namespace App\Support;

final class AppLocale
{
    public const ARABIC = 'ar';

    public const ENGLISH = 'en';

    /**
     * @return list<string>
     */
    public static function supported(): array
    {
        /** @var list<string> $locales */
        $locales = config('rakeeza.locales', [self::ARABIC, self::ENGLISH]);

        return $locales;
    }

    public static function default(): string
    {
        return (string) config('rakeeza.default_locale', self::ARABIC);
    }

    public static function current(): string
    {
        $locale = app()->getLocale();

        return self::isSupported($locale) ? $locale : self::default();
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::supported(), true);
    }

    public static function isRtl(?string $locale = null): bool
    {
        return ($locale ?? self::current()) === self::ARABIC;
    }

    public static function direction(?string $locale = null): string
    {
        return self::isRtl($locale) ? 'rtl' : 'ltr';
    }

    public static function other(): string
    {
        return self::current() === self::ARABIC ? self::ENGLISH : self::ARABIC;
    }
}
