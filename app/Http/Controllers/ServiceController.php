<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\ServiceService;
use App\Services\SettingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class ServiceController extends Controller
{
    public function __invoke(
        string $service,
        ServiceService $serviceService,
        SettingService $settingService,
    ): View|RedirectResponse {
        if (ctype_digit($service)) {
            $model = Service::query()->findOrFail((int) $service);

            return redirect()->route('services.show', $model, 301);
        }

        $service = Service::query()->where('slug', $service)->firstOrFail();

        $service->load([
            'projects' => static fn ($query) => $query->ordered(),
        ]);

        $services = $serviceService->listActive();

        return view('service', [
            'service' => $service,
            'projects' => $service->projects,
            'services' => $services,
            'relatedServices' => $services->reject(
                static fn (Service $item): bool => $item->is($service),
            )->values(),
            'site' => $settingService->publicSite(),
        ]);
    }
}
