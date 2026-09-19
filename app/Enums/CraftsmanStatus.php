<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\AppLocale;

enum CraftsmanStatus: string
{
    case Pending = 'pending';
    case Reviewing = 'reviewing';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(?string $locale = null): string
    {
        $locale ??= AppLocale::ARABIC;

        if ($locale === AppLocale::ENGLISH) {
            return match ($this) {
                self::Pending => 'New',
                self::Reviewing => 'In review',
                self::Accepted => 'Accepted',
                self::Rejected => 'Rejected',
            };
        }

        return match ($this) {
            self::Pending => 'جديد',
            self::Reviewing => 'قيد المراجعة',
            self::Accepted => 'مقبول',
            self::Rejected => 'مرفوض',
        };
    }
}
