<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadStatus: string
{
    case Pending = 'pending';
    case Contacted = 'contacted';
    case Converted = 'converted';
    case Closed = 'closed';
}
