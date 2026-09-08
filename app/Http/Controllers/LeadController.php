<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;

final class LeadController extends Controller
{
    public function store(StoreLeadRequest $request, LeadService $leadService): JsonResponse
    {
        $lead = $leadService->create($request->validated());

        return response()->json([
            'message' => 'تم استلام طلبك بنجاح.',
            'lead_id' => $lead->id,
        ]);
    }
}
