<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;

final class StatisticsController extends Controller
{
    public function __invoke(LeadService $leads): JsonResponse
    {
        return response()->json($leads->statistics());
    }
}
