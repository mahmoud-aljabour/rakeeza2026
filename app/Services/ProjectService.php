<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class ProjectService
{
    /**
     * @return Collection<int, Project>
     */
    public function list(): Collection
    {
        return Project::query()->ordered()->get();
    }

    /**
     * @return LengthAwarePaginator<int, Project>
     */
    public function paginate(int $perPage = 8): LengthAwarePaginator
    {
        return Project::query()
            ->with(['service:id,title'])
            ->ordered()
            ->paginate($perPage);
    }

    public function find(int $id): Project
    {
        return Project::query()->with('service')->findOrFail($id);
    }

    /**
     * @param  array{title: string, details?: string|null, image_path?: string|null, image_paths?: list<string>, order_column?: int, service_id?: int|null}  $data
     */
    public function create(array $data): Project
    {
        return Project::query()->create($data)->load('service');
    }

    /**
     * @param  array{title?: string, details?: string|null, image_path?: string|null, image_paths?: list<string>, order_column?: int, service_id?: int|null}  $data
     */
    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->refresh()->load('service');
    }

    public function delete(Project $project): bool
    {
        return (bool) $project->delete();
    }
}
