<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeadRequest;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LeadController extends Controller
{
    public function __construct(private readonly LeadService $leads) {}

    public function index(Request $request): JsonResponse
    {
        $status = LeadStatus::tryFrom((string) $request->query('status', ''));

        return response()->json($this->leads->list($status));
    }

    public function update(UpdateLeadRequest $request, int $lead): JsonResponse
    {
        $model = $this->leads->find($lead);
        $status = LeadStatus::from($request->validated('status'));

        return response()->json($this->leads->updateStatus($model, $status)->load('service'));
    }

    public function destroy(int $lead): JsonResponse
    {
        $model = $this->leads->find($lead);
        $this->leads->delete($model);

        return response()->json(['message' => 'تم حذف الطلب.']);
    }
}
