<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;

final class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function index(): JsonResponse
    {
        return response()->json($this->settings->publicSite());
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $this->settings->setMany($request->validated());

        return response()->json($this->settings->publicSite());
    }
}
