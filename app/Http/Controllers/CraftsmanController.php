<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCraftsmanRequest;
use App\Services\CraftsmanService;
use App\Services\ServiceService;
use App\Services\SettingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

final class CraftsmanController extends Controller
{
    public function create(SettingService $settings, ServiceService $services): View
    {
        return view('craftsman', [
            'site' => $settings->publicSite(),
            'services' => $services->listActive(),
            'specialties' => $this->specialties($services),
        ]);
    }

    public function store(StoreCraftsmanRequest $request, CraftsmanService $craftsmen): JsonResponse
    {
        $craftsman = $craftsmen->create($request->craftsmanPayload());

        return response()->json([
            'message' => __('site.craftsman.success'),
            'craftsman_id' => $craftsman->id,
        ], 201);
    }

    /**
     * @return array<string, string>
     */
    private function specialties(ServiceService $services): array
    {
        $options = [];

        foreach ($services->listActive() as $service) {
            $options[$service->title] = $service->displayTitle();
        }

        $extras = [
            'سباكة' => __('site.specialty.plumbing'),
            'كهرباء' => __('site.specialty.electrical'),
            'دهان وديكور' => __('site.specialty.painting'),
            'أعمال بناء' => __('site.specialty.construction'),
            'حدادة وألمنيوم' => __('site.specialty.metalwork'),
            'تخصص آخر' => __('site.specialty.other'),
        ];

        foreach ($extras as $value => $label) {
            $options[$value] = $label;
        }

        return $options;
    }
}
