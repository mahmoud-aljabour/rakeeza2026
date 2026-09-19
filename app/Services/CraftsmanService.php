<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CraftsmanStatus;
use App\Models\Craftsman;
use App\Models\CraftsmanNote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class CraftsmanService
{
    /**
     * @return Collection<int, Craftsman>
     */
    public function list(?CraftsmanStatus $status = null): Collection
    {
        return Craftsman::query()
            ->with('notes')
            ->when($status instanceof CraftsmanStatus, fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();
    }

    /**
     * @return Collection<int, Craftsman>
     */
    public function latest(int $limit = 5): Collection
    {
        return Craftsman::query()
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function find(int $id): Craftsman
    {
        return Craftsman::query()->with('notes')->findOrFail($id);
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

    public function updateStatus(
        Craftsman $craftsman,
        CraftsmanStatus $status,
        ?string $note = null,
        ?int $userId = null,
    ): Craftsman {
        return DB::transaction(function () use ($craftsman, $status, $note, $userId): Craftsman {
            $craftsman->update(['status' => $status]);

            CraftsmanNote::query()->create([
                'craftsman_id' => $craftsman->id,
                'status' => $status,
                'note' => filled($note) ? trim($note) : null,
                'user_id' => $userId,
            ]);

            return $craftsman->refresh()->load('notes');
        });
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
