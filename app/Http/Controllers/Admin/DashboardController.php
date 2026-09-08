<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Service;
use App\Scopes\ActiveScope;
use App\Services\CraftsmanService;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;

final class DashboardController extends Controller
{
    public function __invoke(LeadService $leads, CraftsmanService $craftsmen): JsonResponse
    {
        return response()->json([
            'services' => Service::query()->withoutGlobalScope(ActiveScope::class)->count(),
            'active_services' => Service::query()->count(),
            'projects' => Project::query()->count(),
            'leads' => $leads->counts(),
            'craftsmen' => $craftsmen->counts(),
        ]);
    }
}
