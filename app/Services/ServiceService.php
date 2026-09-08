<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;
use App\Scopes\ActiveScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class ServiceService
{
    /**
     * Public catalog: only active services (global scope applied).
     *
     * @return Collection<int, Service>
     */
    public function listActive(): Collection
    {
        return Service::query()->orderBy('id')->get();
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
