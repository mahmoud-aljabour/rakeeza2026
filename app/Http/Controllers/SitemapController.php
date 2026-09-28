<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PrivacyPolicySection;
use App\Models\Service;
use App\Services\PrivacyPolicySectionService;
use App\Services\ServiceService;
use Illuminate\Http\Response;

final class SitemapController extends Controller
{
    public function __invoke(ServiceService $services, PrivacyPolicySectionService $privacy): Response
    {
        $active = $services->listActive();
        $latest = $active->max(fn (Service $service) => $service->updated_at?->getTimestamp() ?? 0);
        $privacyLatest = $privacy->listActive()->max(
            fn (PrivacyPolicySection $section): int => $section->updated_at?->getTimestamp() ?? 0,
        );

        $entries = [[
            'loc' => route('landing'),
            'lastmod' => $latest > 0 ? date(DATE_ATOM, $latest) : now()->toAtomString(),
        ], [
            'loc' => route('privacy'),
            'lastmod' => $privacyLatest > 0 ? date(DATE_ATOM, $privacyLatest) : now()->toAtomString(),
        ], [
            'loc' => route('craftsman.create'),
            'lastmod' => null,
        ]];

        foreach ($active as $service) {
            $entries[] = [
                'loc' => route('services.show', $service),
                'lastmod' => $service->updated_at?->toAtomString() ?? now()->toAtomString(),
            ];
        }

        return response()
            ->view('sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
