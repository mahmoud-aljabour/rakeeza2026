<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadStatus;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'email', 'service_id', 'message', 'status'])]
class Lead extends Model
{
    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * @return BelongsToMany<Service, $this>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->withoutGlobalScope(ActiveScope::class)->withTimestamps();
    }

    /**
     * @return HasMany<LeadNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function servicesLabel(string $separator = '، '): string
    {
        $titles = $this->relationLoaded('services')
            ? $this->services->pluck('title')->all()
            : [];

        if ($titles === [] && $this->service) {
            $titles = [$this->service->title];
        }

        return $titles !== [] ? implode($separator, $titles) : 'استفسار عام';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
        ];
    }
}
