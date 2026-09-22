<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrivacyPolicySectionRequest;
use App\Http\Requests\UpdatePrivacyPolicySectionRequest;
use App\Services\PrivacyPolicySectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PrivacyPolicySectionController extends Controller
{
    public function __construct(
        private readonly PrivacyPolicySectionService $sections,
    ) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            return response()->json($this->sections->listAll());
        }

        $perPage = min(24, max(6, $request->integer('per_page', 12)));

        return response()->json($this->sections->paginateAll($perPage));
    }

    public function store(StorePrivacyPolicySectionRequest $request): JsonResponse
    {
        return response()->json($this->sections->create($request->validated()), 201);
    }

    public function show(int $section): JsonResponse
    {
        return response()->json($this->sections->find($section));
    }

    public function update(UpdatePrivacyPolicySectionRequest $request, int $section): JsonResponse
    {
        $model = $this->sections->find($section);

        return response()->json($this->sections->update($model, $request->validated()));
    }

    public function destroy(int $section): JsonResponse
    {
        $this->sections->delete($this->sections->find($section));

        return response()->json(['message' => 'تم حذف البند.']);
    }
}
