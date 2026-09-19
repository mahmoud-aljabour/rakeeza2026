<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\AppLocale;

enum LeadStatus: string
{
    case Pending = 'pending';
    case Contacted = 'contacted';
    case Completed = 'completed';
    case Closed = 'closed';

    public function label(?string $locale = null): string
    {
        $locale ??= AppLocale::ARABIC;

        if ($locale === AppLocale::ENGLISH) {
            return match ($this) {
                self::Pending => 'New',
                self::Contacted => 'Contacted',
                self::Completed => 'Completed',
                self::Closed => 'Closed',
            };
        }

        return match ($this) {
            self::Pending => 'جديد',
            self::Contacted => 'تم التواصل',
            self::Completed => 'منجز',
            self::Closed => 'مغلق',
        };
    }
}
