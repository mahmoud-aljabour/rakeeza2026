<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProjectService;
use App\Services\ServiceService;
use App\Services\SettingService;
use Illuminate\Contracts\View\View;

final class LandingController extends Controller
{
    public function __invoke(
        ServiceService $serviceService,
        ProjectService $projectService,
        SettingService $settingService,
    ): View {
        return view('landing', [
            'services' => $serviceService->listActive(),
            'projects' => $projectService->list(),
            'site' => $settingService->publicSite(),
        ]);
    }
}
