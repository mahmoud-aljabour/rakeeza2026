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
        $craftsman = $craftsmen->create($request->validated());

        return response()->json([
            'message' => 'تم استلام طلب انضمامك، وسيتواصل معك فريق ركيزة قريباً.',
            'craftsman_id' => $craftsman->id,
        ], 201);
    }

    /**
     * @return list<string>
     */
    private function specialties(ServiceService $services): array
    {
        $fromCatalog = $services->listActive()->pluck('title')->all();

        return array_values(array_unique([
            ...$fromCatalog,
            'سباكة',
            'كهرباء',
            'دهان وديكور',
            'أعمال بناء',
            'حدادة وألمنيوم',
            'تخصص آخر',
        ]));
    }
}
