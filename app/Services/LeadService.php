<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Collection;

final class LeadService
{
    /**
     * @return Collection<int, Lead>
     */
    public function list(?LeadStatus $status = null): Collection
    {
        return Lead::query()
            ->with('service')
            ->when($status instanceof LeadStatus, fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();
    }

    public function find(int $id): Lead
    {
        return Lead::query()->with('service')->findOrFail($id);
    }

    /**
     * @param  array{name: string, phone: string, service_id?: int|null, message?: string|null, status?: LeadStatus|string}  $data
     */
    public function create(array $data): Lead
    {
        $data['status'] ??= LeadStatus::Pending;

        return Lead::query()->create($data);
    }

    /**
     * @param  array{name?: string, phone?: string, service_id?: int, message?: string|null, status?: LeadStatus|string}  $data
     */
    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->refresh();
    }

    public function updateStatus(Lead $lead, LeadStatus $status): Lead
    {
        $lead->update(['status' => $status]);

        return $lead->refresh();
    }

    public function delete(Lead $lead): bool
    {
        return (bool) $lead->delete();
    }

    /**
     * @return array{total: int, pending: int, contacted: int, converted: int, closed: int}
     */
    public function counts(): array
    {
        return [
            'total' => Lead::query()->count(),
            'pending' => Lead::query()->where('status', LeadStatus::Pending)->count(),
            'contacted' => Lead::query()->where('status', LeadStatus::Contacted)->count(),
            'converted' => Lead::query()->where('status', LeadStatus::Converted)->count(),
            'closed' => Lead::query()->where('status', LeadStatus::Closed)->count(),
        ];
    }
}
