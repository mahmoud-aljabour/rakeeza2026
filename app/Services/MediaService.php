<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\PublicImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class MediaService
{
    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    public function replace(?string $currentPath, UploadedFile $file, string $directory): string
    {
        $this->delete($currentPath);

        return $this->store($file, $directory);
    }

    public function delete(?string $path): void
    {
        if (! PublicImage::isManaged($path)) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * @param  list<string|null>  $paths
     */
    public function deleteMany(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }

    public function fromUploadOrUrl(?UploadedFile $file, ?string $url, string $directory, ?string $currentPath = null): ?string
    {
        if ($file instanceof UploadedFile) {
            return $currentPath === null
                ? $this->store($file, $directory)
                : $this->replace($currentPath, $file, $directory);
        }

        $url = is_string($url) ? trim($url) : '';

        if ($url === '') {
            return null;
        }

        $this->delete($currentPath);

        return $url;
    }
}
