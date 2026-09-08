<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Services\MediaService;
use App\Services\ServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceService $services,
        private readonly MediaService $media,
    ) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            return response()->json($this->services->listAll());
        }

        $perPage = min(24, max(6, $request->integer('per_page', 9)));

        return response()->json($this->services->paginateAll($perPage));
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['image', 'image_url']);
        $imagePath = $this->media->fromUploadOrUrl(
            $request->file('image'),
            $request->validated('image_url'),
            'services',
        );

        if ($imagePath !== null) {
            $data['image_path'] = $imagePath;
        }

        return response()->json($this->services->create($data), 201);
    }

    public function show(int $service): JsonResponse
    {
        return response()->json($this->services->find($service));
    }

    public function update(UpdateServiceRequest $request, int $service): JsonResponse
    {
        $model = $this->services->find($service);
        $data = $request->safe()->except(['image', 'image_url']);
        $imagePath = $this->media->fromUploadOrUrl(
            $request->file('image'),
            $request->validated('image_url'),
            'services',
            $model->image_path,
        );

        if ($imagePath !== null) {
            $data['image_path'] = $imagePath;
        }

        return response()->json($this->services->update($model, $data));
    }

    public function destroy(int $service): JsonResponse
    {
        $model = $this->services->find($service);

        if ($model->leads()->exists()) {
            return response()->json([
                'message' => 'لا يمكن حذف الخدمة لوجود طلبات مرتبطة بها.',
            ], 422);
        }

        $this->media->delete($model->image_path);
        $this->services->delete($model);

        return response()->json(['message' => 'تم حذف الخدمة.']);
    }
}
