<?php

declare(strict_types=1);

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Support\PublicImage;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'details', 'image_path', 'image_paths', 'order_column', 'service_id'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_column')->orderBy('id');
    }

    /**
     * @return list<string>
     */
    public function storedImagePaths(): array
    {
        $paths = $this->image_paths;

        if (is_array($paths) && $paths !== []) {
            return array_values(array_filter(
                $paths,
                static fn (mixed $path): bool => is_string($path) && $path !== '',
            ));
        }

        return $this->image_path ? [$this->image_path] : [];
    }

    /**
     * @return list<string>
     */
    public function imageUrls(): array
    {
        return array_map(
            static fn (string $path): string => PublicImage::url($path),
            $this->storedImagePaths(),
        );
    }

    public function imageUrl(): string
    {
        return PublicImage::url($this->storedImagePaths()[0] ?? $this->image_path);
    }

    /**
     * @return array{title: string, details: string, images: list<string>}
     */
    public function lightboxPayload(): array
    {
        $images = $this->imageUrls();

        return [
            'title' => $this->title,
            'details' => $this->details ?? '',
            'images' => $images !== [] ? $images : [$this->imageUrl()],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'order_column' => 'integer',
            'image_paths' => 'array',
        ];
    }

    /**
     * @var list<string>
     */
    protected $appends = ['image_url', 'image_urls', 'images_count'];

    public function getImagesCountAttribute(): int
    {
        return count($this->storedImagePaths());
    }

    public function getImageUrlAttribute(): string
    {
        return $this->imageUrl();
    }

    /**
     * @return list<string>
     */
    public function getImageUrlsAttribute(): array
    {
        return $this->imageUrls();
    }
}
