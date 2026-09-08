<?php

declare(strict_types=1);

namespace App\Enums;

enum CraftsmanStatus: string
{
    case Pending = 'pending';
    case Reviewing = 'reviewing';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'جديد',
            self::Reviewing => 'قيد المراجعة',
            self::Accepted => 'مقبول',
            self::Rejected => 'مرفوض',
        };
    }
}
