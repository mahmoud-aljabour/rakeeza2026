<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\ServiceService;
use App\Services\SettingService;
use Illuminate\Contracts\View\View;

final class ServiceController extends Controller
{
    public function __invoke(
        Service $service,
        ServiceService $serviceService,
        SettingService $settingService,
    ): View {
        $service->load([
            'projects' => static fn ($query) => $query->ordered(),
        ]);

        return view('service', [
            'service' => $service,
            'projects' => $service->projects,
            'services' => $serviceService->listActive(),
            'site' => $settingService->publicSite(),
        ]);
    }
}
