<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CraftsmanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCraftsmanRequest;
use App\Services\CraftsmanService;
use App\Services\CraftsmanWordExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class CraftsmanController extends Controller
{
    public function __construct(
        private readonly CraftsmanService $craftsmen,
        private readonly CraftsmanWordExportService $wordExport,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $status = CraftsmanStatus::tryFrom((string) $request->query('status', ''));

        return response()->json($this->craftsmen->list($status));
    }

    public function export(Request $request): Response
    {
        $status = CraftsmanStatus::tryFrom((string) $request->query('status', ''));

        return $this->wordExport->downloadList($this->craftsmen->list($status), $status);
    }

    public function exportOne(int $craftsman): Response
    {
        return $this->wordExport->downloadOne($this->craftsmen->find($craftsman));
    }

    public function update(UpdateCraftsmanRequest $request, int $craftsman): JsonResponse
    {
        $model = $this->craftsmen->find($craftsman);
        $status = CraftsmanStatus::from($request->validated('status'));

        return response()->json($this->craftsmen->updateStatus($model, $status));
    }

    public function destroy(int $craftsman): JsonResponse
    {
        $model = $this->craftsmen->find($craftsman);
        $this->craftsmen->delete($model);

        return response()->json(['message' => 'تم حذف طلب الحرفي.']);
    }
}
