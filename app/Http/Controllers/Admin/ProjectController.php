<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\MediaService;
use App\Services\ProjectService;
use App\Support\PublicImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projects,
        private readonly MediaService $media,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min(24, max(6, $request->integer('per_page', 8)));

        return response()->json($this->projects->paginate($perPage));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['items', 'sync_images']);
        $paths = $this->resolveImagePaths($request);

        $data['image_paths'] = $paths;
        $data['image_path'] = $paths[0] ?? null;
        $data['order_column'] = (int) ($data['order_column'] ?? 0);

        return response()->json($this->projects->create($data), 201);
    }

    public function show(int $project): JsonResponse
    {
        return response()->json($this->projects->find($project));
    }

    public function update(UpdateProjectRequest $request, int $project): JsonResponse
    {
        $model = $this->projects->find($project);
        $data = $request->safe()->except(['items', 'sync_images']);

        if ($request->boolean('sync_images') || $request->exists('items')) {
            $paths = $this->resolveImagePaths($request, $model);
            $this->media->deleteMany(array_values(array_diff($model->storedImagePaths(), $paths)));
            $data['image_paths'] = $paths;
            $data['image_path'] = $paths[0] ?? null;
        }

        if (array_key_exists('order_column', $data)) {
            $data['order_column'] = (int) $data['order_column'];
        }

        return response()->json($this->projects->update($model, $data));
    }

    public function destroy(int $project): JsonResponse
    {
        $model = $this->projects->find($project);
        $this->media->deleteMany($model->storedImagePaths());
        $this->projects->delete($model);

        return response()->json(['message' => 'تم حذف المشروع.']);
    }

    /**
     * @return list<string>
     */
    private function resolveImagePaths(StoreProjectRequest $request, ?Project $project = null): array
    {
        $allowedExisting = $project?->storedImagePaths() ?? [];
        $paths = [];

        foreach ($request->input('items', []) as $index => $item) {
            $source = $item['source'] ?? '';

            if ($source === 'url') {
                $url = trim((string) ($item['url'] ?? ''));

                if (PublicImage::isAcceptableReference($url)) {
                    $paths[] = $url;
                }

                continue;
            }

            if ($source === 'existing') {
                $path = trim((string) ($item['path'] ?? ''));

                if (in_array($path, $allowedExisting, true)) {
                    $paths[] = $path;
                }

                continue;
            }

            if ($source === 'file') {
                $file = $request->file("items.$index.image");

                if ($file instanceof UploadedFile) {
                    $paths[] = $this->media->store($file, 'projects');
                }
            }
        }

        return array_values(array_unique($paths));
    }
}
