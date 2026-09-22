<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadNote;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class LeadService
{
    /**
     * @return Collection<int, Lead>
     */
    public function list(?LeadStatus $status = null, ?string $from = null, ?string $to = null): Collection
    {
        return $this->filteredQuery($status, $from, $to)
            ->latest()
            ->get();
    }

    /**
     * @return LengthAwarePaginator<int, Lead>
     */
    public function paginate(
        ?LeadStatus $status = null,
        int $perPage = 10,
        ?string $from = null,
        ?string $to = null,
    ): LengthAwarePaginator {
        return $this->filteredQuery($status, $from, $to)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @return Builder<Lead>
     */
    private function filteredQuery(?LeadStatus $status = null, ?string $from = null, ?string $to = null): Builder
    {
        return Lead::query()
            ->with(['service', 'services', 'notes'])
            ->when($status instanceof LeadStatus, fn ($query) => $query->where('status', $status))
            ->when(
                filled($from),
                fn ($query) => $query->where('created_at', '>=', $from.' 00:00:00'),
            )
            ->when(
                filled($to),
                fn ($query) => $query->where('created_at', '<=', $to.' 23:59:59'),
            );
    }

    /**
     * @return Collection<int, Lead>
     */
    public function latest(int $limit = 5): Collection
    {
        return Lead::query()
            ->with(['service', 'services'])
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function find(int $id): Lead
    {
        return Lead::query()->with(['service', 'services', 'notes'])->findOrFail($id);
    }

    /**
     * @param  array{name: string, phone: string, email?: string|null, service_id?: int|null, service_ids?: list<int>, message?: string|null, status?: LeadStatus|string}  $data
     */
    public function create(array $data): Lead
    {
        $serviceIds = array_values(array_filter(
            $data['service_ids'] ?? (($data['service_id'] ?? null) ? [(int) $data['service_id']] : []),
            static fn (mixed $id): bool => is_int($id) || ctype_digit((string) $id),
        ));
        $serviceIds = array_map(static fn (mixed $id): int => (int) $id, $serviceIds);
        unset($data['service_ids']);

        $data['status'] ??= LeadStatus::Pending;
        $data['service_id'] = $serviceIds[0] ?? ($data['service_id'] ?? null);

        return DB::transaction(function () use ($data, $serviceIds): Lead {
            $lead = Lead::query()->create($data);
            $lead->services()->sync($serviceIds);

            return $lead->load(['service', 'services', 'notes']);
        });
    }

    /**
     * @param  array{name: string, phone: string, email?: string|null, service_id?: int|null, service_ids?: list<int>, message?: string|null}  $data
     */
    public function createManual(array $data, ?string $note = null, ?int $userId = null): Lead
    {
        return DB::transaction(function () use ($data, $note, $userId): Lead {
            $serviceIds = array_values(array_filter(
                $data['service_ids'] ?? (($data['service_id'] ?? null) ? [(int) $data['service_id']] : []),
                static fn (mixed $id): bool => is_int($id) || ctype_digit((string) $id),
            ));
            $serviceIds = array_map(static fn (mixed $id): int => (int) $id, $serviceIds);
            unset($data['service_ids']);

            $data['status'] ??= LeadStatus::Pending;
            $data['email'] = filled($data['email'] ?? null) ? $data['email'] : null;
            $data['message'] = filled($data['message'] ?? null) ? $data['message'] : null;
            $data['service_id'] = $serviceIds[0] ?? ($data['service_id'] ?? null);

            $lead = Lead::query()->create($data);
            $lead->services()->sync($serviceIds);

            if (filled($note)) {
                LeadNote::query()->create([
                    'lead_id' => $lead->id,
                    'status' => $lead->status,
                    'note' => trim($note),
                    'user_id' => $userId,
                ]);
            }

            return $lead->load(['service', 'services', 'notes']);
        });
    }

    /**
     * @param  array{name?: string, phone?: string, email?: string|null, service_id?: int, message?: string|null, status?: LeadStatus|string}  $data
     */
    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->refresh();
    }

    public function updateStatus(
        Lead $lead,
        LeadStatus $status,
        ?string $note = null,
        ?int $userId = null,
        ?string $completedPrice = null,
    ): Lead {
        return DB::transaction(function () use ($lead, $status, $note, $userId, $completedPrice): Lead {
            $isCompleted = $status === LeadStatus::Completed;

            $lead->update([
                'status' => $status,
                'completed_price' => $isCompleted ? $completedPrice : null,
                'completed_at' => $isCompleted ? now() : null,
            ]);

            LeadNote::query()->create([
                'lead_id' => $lead->id,
                'status' => $status,
                'note' => filled($note) ? trim($note) : null,
                'user_id' => $userId,
            ]);

            return $lead->refresh()->load(['service', 'services', 'notes']);
        });
    }

    /**
     * @return array{
     *     summary: array{
     *         total: int,
     *         pending: int,
     *         contacted: int,
     *         completed: int,
     *         closed: int,
     *         completed_count: int,
     *         total_revenue: float
     *     },
     *     completed_leads: Collection<int, Lead>
     * }
     */
    public function statistics(): array
    {
        $counts = $this->counts();

        $completedLeads = Lead::query()
            ->with(['service', 'services'])
            ->where('status', LeadStatus::Completed)
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get();

        return [
            'summary' => [
                ...$counts,
                'completed_count' => $counts['completed'],
                'total_revenue' => (float) $completedLeads->sum('completed_price'),
            ],
            'completed_leads' => $completedLeads,
        ];
    }

    public function delete(Lead $lead): bool
    {
        return (bool) $lead->delete();
    }

    /**
     * @return array{total: int, pending: int, contacted: int, completed: int, closed: int}
     */
    public function counts(): array
    {
        $countsByStatus = Lead::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $pending = (int) $countsByStatus->get(LeadStatus::Pending->value, 0);
        $contacted = (int) $countsByStatus->get(LeadStatus::Contacted->value, 0);
        $completed = (int) $countsByStatus->get(LeadStatus::Completed->value, 0);
        $closed = (int) $countsByStatus->get(LeadStatus::Closed->value, 0);

        return [
            'total' => $pending + $contacted + $completed + $closed,
            'pending' => $pending,
            'contacted' => $contacted,
            'completed' => $completed,
            'closed' => $closed,
        ];
    }
}
