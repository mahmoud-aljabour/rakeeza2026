<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CraftsmanStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'city', 'specialty', 'experience_years', 'has_tools', 'bio', 'status'])]
class Craftsman extends Model
{
    /**
     * @return HasMany<CraftsmanNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(CraftsmanNote::class)->latest();
    }

    /**
     * @return list<string>
     */
    public function specialtiesList(): array
    {
        $value = $this->specialty;

        if (is_array($value)) {
            return array_values(array_filter(
                $value,
                static fn (mixed $item): bool => is_string($item) && $item !== '',
            ));
        }

        if (is_string($value) && $value !== '') {
            return [$value];
        }

        return [];
    }

    public function specialtiesLabel(string $separator = '، '): string
    {
        return implode($separator, $this->specialtiesList());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'has_tools' => 'boolean',
            'status' => CraftsmanStatus::class,
            'specialty' => 'array',
        ];
    }
}
