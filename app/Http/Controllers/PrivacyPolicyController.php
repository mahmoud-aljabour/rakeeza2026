<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PrivacyPolicySectionService;
use App\Services\ServiceService;
use App\Services\SettingService;
use Illuminate\Contracts\View\View;

final class PrivacyPolicyController extends Controller
{
    public function __invoke(
        PrivacyPolicySectionService $sections,
        ServiceService $services,
        SettingService $settings,
    ): View {
        $items = $sections->listActive();

        return view('privacy-policy', [
            'sections' => $items,
            'services' => $services->listActive(),
            'site' => $settings->publicSite(),
        ]);
    }
}
