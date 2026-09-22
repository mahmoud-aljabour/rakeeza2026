<?php

declare(strict_types=1);

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Support\AppLocale;
use App\Support\PublicImage;
use App\Support\TranslatesCatalog;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'title_en',
    'slug',
    'description',
    'description_en',
    'body',
    'body_en',
    'seo_title',
    'seo_title_en',
    'meta_description',
    'meta_description_en',
    'image_path',
    'is_active',
    'projects_count',
])]
#[ScopedBy([ActiveScope::class])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service): void {
            if (filled($service->slug)) {
                return;
            }

            $source = filled($service->title_en) ? (string) $service->title_en : (string) $service->title;
            $service->slug = self::uniqueSlug($source, $service->exists ? $service->id : null);
        });
    }

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

    public function displayTitle(): string
    {
        return TranslatesCatalog::text(
            'catalog.services.'.$this->title.'.title',
            $this->title,
            $this->title_en,
        );
    }

    public function displayDescription(): string
    {
        return TranslatesCatalog::text(
            'catalog.services.'.$this->title.'.description',
            (string) $this->description,
            $this->description_en,
        );
    }

    public function displayBody(): string
    {
        return $this->localized($this->body, $this->body_en);
    }

    /**
     * @return list<string>
     */
    public function bodyParagraphs(): array
    {
        $body = trim($this->displayBody());

        if ($body === '') {
            return [];
        }

        $parts = preg_split("/\R\s*\R/u", $body) ?: [];

        return array_values(array_filter(
            array_map(trim(...), $parts),
            fn (string $paragraph): bool => $paragraph !== '',
        ));
    }

    public function pageHeading(): string
    {
        return __('site.services.page_heading', [
            'service' => $this->displayTitle(),
        ]);
    }

    public function seoTitle(): string
    {
        $stored = $this->localized($this->seo_title, $this->seo_title_en);

        if ($stored !== '') {
            return $stored;
        }

        return __('site.services.default_seo_title', [
            'service' => $this->displayTitle(),
            'brand' => __('site.brand'),
        ]);
    }

    public function metaDescription(): string
    {
        $stored = $this->localized($this->meta_description, $this->meta_description_en);

        if ($stored !== '') {
            return $stored;
        }

        $short = trim($this->displayDescription());

        if (mb_strlen($short) <= 160) {
            return $short;
        }

        return mb_substr($short, 0, 157).'...';
    }

    public function imageAlt(): string
    {
        return __('site.services.image_alt', [
            'service' => $this->displayTitle(),
            'brand' => __('site.brand'),
        ]);
    }

    private function localized(?string $arabic, ?string $english): string
    {
        if (AppLocale::current() === AppLocale::ENGLISH && filled($english)) {
            return $english;
        }

        return trim((string) $arabic);
    }

    private static function uniqueSlug(string $source, ?int $ignoreId): string
    {
        $base = Str::slug($source);

        if ($base === '' || ctype_digit($base)) {
            $base = 'service';
        }

        $slug = $base;
        $suffix = 2;

        while (self::slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private static function slugExists(string $slug, ?int $ignoreId): bool
    {
        return self::query()
            ->withInactive()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'projects_count' => 'integer',
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
