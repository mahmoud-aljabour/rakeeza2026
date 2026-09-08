<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CraftsmanStatus;
use App\Models\Craftsman;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

final class CraftsmanWordExportService
{
    /**
     * @param  Collection<int, Craftsman>  $craftsmen
     */
    public function downloadList(Collection $craftsmen, ?CraftsmanStatus $status = null): Response
    {
        $html = view('admin.exports.craftsmen', [
            'craftsmen' => $craftsmen,
            'statusLabel' => $status?->label() ?? 'الكل',
            'exportedAt' => now()->format('Y-m-d H:i'),
        ])->render();

        return $this->wordResponse($html, 'طلبات-الحرفيين-'.now()->format('Y-m-d').'.doc');
    }

    public function downloadOne(Craftsman $craftsman): Response
    {
        $html = view('admin.exports.craftsman', [
            'craftsman' => $craftsman,
            'exportedAt' => now()->format('Y-m-d H:i'),
        ])->render();

        $safeName = preg_replace('/[^\p{L}\p{N}\-_]+/u', '-', $craftsman->name) ?: 'حرفي';

        return $this->wordResponse($html, 'طلب-حرفي-'.$safeName.'.doc');
    }

    private function wordResponse(string $html, string $filename): Response
    {
        return response($html, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"; filename*=UTF-8''".rawurlencode($filename),
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
