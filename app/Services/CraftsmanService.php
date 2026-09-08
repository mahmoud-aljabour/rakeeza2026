<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CraftsmanStatus;
use App\Models\Craftsman;
use Illuminate\Database\Eloquent\Collection;

final class CraftsmanService
{
    /**
     * @return Collection<int, Craftsman>
     */
    public function list(?CraftsmanStatus $status = null): Collection
    {
        return Craftsman::query()
            ->when($status instanceof CraftsmanStatus, fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();
    }

    public function find(int $id): Craftsman
    {
        return Craftsman::query()->findOrFail($id);
    }

    /**
     * @param  array{name: string, phone: string, city: string, specialty: string, experience_years?: int, has_tools?: bool, bio?: string|null, status?: CraftsmanStatus|string}  $data
     */
    public function create(array $data): Craftsman
    {
        $data['status'] ??= CraftsmanStatus::Pending;
        $data['experience_years'] ??= 0;
        $data['has_tools'] ??= false;

        return Craftsman::query()->create($data);
    }

    public function updateStatus(Craftsman $craftsman, CraftsmanStatus $status): Craftsman
    {
        $craftsman->update(['status' => $status]);

        return $craftsman->refresh();
    }

    public function delete(Craftsman $craftsman): bool
    {
        return (bool) $craftsman->delete();
    }

    /**
     * @return array{total: int, pending: int, reviewing: int, accepted: int, rejected: int}
     */
    public function counts(): array
    {
        return [
            'total' => Craftsman::query()->count(),
            'pending' => Craftsman::query()->where('status', CraftsmanStatus::Pending)->count(),
            'reviewing' => Craftsman::query()->where('status', CraftsmanStatus::Reviewing)->count(),
            'accepted' => Craftsman::query()->where('status', CraftsmanStatus::Accepted)->count(),
            'rejected' => Craftsman::query()->where('status', CraftsmanStatus::Rejected)->count(),
        ];
    }
}
