<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CraftsmanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCraftsmanRequest;
use App\Services\CraftsmanExcelExportService;
use App\Services\CraftsmanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class CraftsmanController extends Controller
{
    public function __construct(
        private readonly CraftsmanService $craftsmen,
        private readonly CraftsmanExcelExportService $excelExport,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $status = CraftsmanStatus::tryFrom((string) $request->query('status', ''));
        $perPage = min(50, max(5, $request->integer('per_page', 10)));

        return response()->json($this->craftsmen->paginate($status, $perPage));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $status = CraftsmanStatus::tryFrom((string) $request->query('status', ''));

        return $this->excelExport->downloadList($this->craftsmen->list($status), $status);
    }

    public function update(UpdateCraftsmanRequest $request, int $craftsman): JsonResponse
    {
        $model = $this->craftsmen->find($craftsman);
        $validated = $request->validated();
        $status = CraftsmanStatus::from($validated['status']);
        $note = isset($validated['note']) && $validated['note'] !== ''
            ? $validated['note']
            : null;

        return response()->json(
            $this->craftsmen->updateStatus($model, $status, $note, $request->user()?->id),
        );
    }

    public function destroy(int $craftsman): JsonResponse
    {
        $model = $this->craftsmen->find($craftsman);
        $this->craftsmen->delete($model);

        return response()->json(['message' => 'تم حذف طلب الحرفي.']);
    }
}
