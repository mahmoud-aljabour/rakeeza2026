<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PrivacyPolicySection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class PrivacyPolicySectionService
{
    /**
     * @return Collection<int, PrivacyPolicySection>
     */
    public function listActive(): Collection
    {
        return PrivacyPolicySection::query()->ordered()->get();
    }

    /**
     * @return Collection<int, PrivacyPolicySection>
     */
    public function listAll(): Collection
    {
        return PrivacyPolicySection::query()
            ->withInactive()
            ->ordered()
            ->get();
    }

    /**
     * @return LengthAwarePaginator<int, PrivacyPolicySection>
     */
    public function paginateAll(int $perPage = 12): LengthAwarePaginator
    {
        return PrivacyPolicySection::query()
            ->withInactive()
            ->ordered()
            ->paginate($perPage);
    }

    public function find(int $id): PrivacyPolicySection
    {
        return PrivacyPolicySection::query()
            ->withInactive()
            ->findOrFail($id);
    }

    /**
     * @param  array{title: string, title_en?: string|null, description: string, description_en?: string|null, order_column?: int, is_active?: bool}  $data
     */
    public function create(array $data): PrivacyPolicySection
    {
        return PrivacyPolicySection::query()->create($data);
    }

    /**
     * @param  array{title?: string, title_en?: string|null, description?: string, description_en?: string|null, order_column?: int, is_active?: bool}  $data
     */
    public function update(PrivacyPolicySection $section, array $data): PrivacyPolicySection
    {
        $section->update($data);

        return $section->refresh();
    }

    public function delete(PrivacyPolicySection $section): bool
    {
        return (bool) $section->delete();
    }
}
