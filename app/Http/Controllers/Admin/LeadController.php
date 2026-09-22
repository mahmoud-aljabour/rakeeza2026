<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Services\LeadExcelExportService;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class LeadController extends Controller
{
    public function __construct(
        private readonly LeadService $leads,
        private readonly LeadExcelExportService $excelExport,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $status = LeadStatus::tryFrom((string) ($validated['status'] ?? ''));
        $perPage = min(50, max(5, (int) ($validated['per_page'] ?? 10)));
        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        return response()->json($this->leads->paginate($status, $perPage, $from, $to));
    }

    public function store(AdminStoreLeadRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $note = isset($validated['note']) && $validated['note'] !== ''
            ? $validated['note']
            : null;
        unset($validated['note']);

        $lead = $this->leads->createManual($validated, $note, $request->user()?->id);

        return response()->json($lead, 201);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $status = LeadStatus::tryFrom((string) ($validated['status'] ?? ''));
        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        return $this->excelExport->downloadList(
            $this->leads->list($status, $from, $to),
            $status,
            $from,
            $to,
        );
    }

    public function update(UpdateLeadRequest $request, int $lead): JsonResponse
    {
        $model = $this->leads->find($lead);
        $validated = $request->validated();
        $status = LeadStatus::from($validated['status']);
        $note = isset($validated['note']) && $validated['note'] !== ''
            ? $validated['note']
            : null;
        $completedPrice = isset($validated['completed_price'])
            ? (string) $validated['completed_price']
            : null;

        return response()->json(
            $this->leads->updateStatus(
                $model,
                $status,
                $note,
                $request->user()?->id,
                $completedPrice,
            ),
        );
    }

    public function destroy(int $lead): JsonResponse
    {
        $model = $this->leads->find($lead);
        $this->leads->delete($model);

        return response()->json(['message' => 'تم حذف الطلب.']);
    }
}
