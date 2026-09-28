<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;
use App\Scopes\ActiveScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class ServiceService
{
    /**
     * Columns the public header, footer, cards, forms, and sitemap read.
     *
     * @var list<string>
     */
    private const PUBLIC_COLUMNS = [
        'id',
        'title',
        'title_en',
        'slug',
        'description',
        'description_en',
        'image_path',
        'projects_count',
        'is_active',
        'updated_at',
    ];

    /**
     * Public catalog: only active services (global scope applied).
     *
     * Rows are cached as plain arrays because the cache store refuses to unserialize objects.
     *
     * @return Collection<int, Service>
     */
    public function listActive(): Collection
    {
        $rows = once(fn (): array => Cache::remember(
            Service::ACTIVE_CACHE_KEY,
            now()->addDay(),
            fn (): array => Service::query()
                ->select(self::PUBLIC_COLUMNS)
                ->orderBy('id')
                ->toBase()
                ->get()
                ->map(static fn (object $row): array => (array) $row)
                ->all(),
        ));

        return Service::hydrate($rows);
    }

    /**
     * Admin listing: include inactive services.
     *
     * @return Collection<int, Service>
     */
    public function listAll(): Collection
    {
        return Service::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->orderBy('id')
            ->get();
    }

    /**
     * @return LengthAwarePaginator<int, Service>
     */
    public function paginateAll(int $perPage = 9): LengthAwarePaginator
    {
        return Service::query()
            ->withInactive()
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function findActive(int $id): Service
    {
        return Service::query()->findOrFail($id);
    }

    public function find(int $id): Service
    {
        return Service::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->findOrFail($id);
    }

    /**
     * @param  array{title: string, description?: string|null, image_path?: string|null, is_active?: bool}  $data
     */
    public function create(array $data): Service
    {
        return Service::query()->create($data);
    }

    /**
     * @param  array{title?: string, description?: string|null, image_path?: string|null, is_active?: bool}  $data
     */
    public function update(Service $service, array $data): Service
    {
        $service->update($data);

        return $service->refresh();
    }

    public function delete(Service $service): bool
    {
        return (bool) $service->delete();
    }
}
