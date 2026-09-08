<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CraftsmanStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'phone', 'city', 'specialty', 'experience_years', 'has_tools', 'bio', 'status'])]
class Craftsman extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'has_tools' => 'boolean',
            'status' => CraftsmanStatus::class,
        ];
    }
}
