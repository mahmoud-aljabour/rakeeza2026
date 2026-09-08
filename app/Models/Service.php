<?php

declare(strict_types=1);

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Support\PublicImage;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'image_path', 'is_active'])]
#[ScopedBy([ActiveScope::class])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    /**
     * @return HasMany<Lead, $this>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * @param  Builder<Service>  $query
     * @return Builder<Service>
     */
    public function scopeWithInactive(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ActiveScope::class);
    }

    public function imageUrl(): string
    {
        return PublicImage::url($this->image_path);
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @var list<string>
     */
    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        return $this->imageUrl();
    }
}
