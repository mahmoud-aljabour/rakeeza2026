<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;

final class LeadController extends Controller
{
    public function store(
        StoreLeadRequest $request,
        LeadService $leadService,
    ): JsonResponse {
        if (filled($request->input('website'))) {
            return response()->json([
                'success' => true,
                'message' => __('site.form.submission_success'),
            ]);
        }

        $lead = $leadService->create($request->leadPayload());

        return response()->json([
            'success' => true,
            'message' => __('site.form.submission_success'),
            'lead_id' => $lead->id,
        ]);
    }
}
