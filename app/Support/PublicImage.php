<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class PublicImage
{
    public static function url(?string $path): string
    {
        if ($path === null || $path === '') {
            return asset('images/logo.png');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    public static function isManaged(?string $path): bool
    {
        return is_string($path)
            && $path !== ''
            && ! str_starts_with($path, 'images/')
            && ! self::isLinked($path);
    }

    public static function isLinked(?string $path): bool
    {
        return is_string($path)
            && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'));
    }

    public static function isAcceptableReference(?string $path): bool
    {
        if (! is_string($path) || $path === '') {
            return false;
        }

        if (str_starts_with($path, 'images/')) {
            return true;
        }

        return self::isLinked($path) && filter_var($path, FILTER_VALIDATE_URL) !== false;
    }
}
