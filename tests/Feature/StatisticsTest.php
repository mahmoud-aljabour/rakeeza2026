<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_statistics(): void
    {
        $this->getJson('/api/admin/statistics')->assertUnauthorized();
    }

    public function test_statistics_lists_completed_leads_and_totals_their_revenue(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['title' => 'صيانة']);
        $olderCompletedLead = Lead::query()->create([
            'name' => 'أحمد',
            'phone' => '0591000000',
            'service_id' => $service->id,
            'status' => LeadStatus::Completed,
            'completed_price' => '125.25',
            'completed_at' => now()->subDay(),
        ]);
        $latestCompletedLead = Lead::query()->create([
            'name' => 'سارة',
            'phone' => '0592000000',
            'service_id' => $service->id,
            'status' => LeadStatus::Completed,
            'completed_price' => '200.25',
            'completed_at' => now(),
        ]);
        Lead::query()->create([
            'name' => 'طلب مفتوح',
            'phone' => '0593000000',
            'service_id' => $service->id,
            'status' => LeadStatus::Pending,
        ]);
        Lead::query()->create([
            'name' => 'تم التواصل',
            'phone' => '0594000000',
            'service_id' => $service->id,
            'status' => LeadStatus::Contacted,
        ]);
        Lead::query()->create([
            'name' => 'مغلق',
            'phone' => '0595000000',
            'service_id' => $service->id,
            'status' => LeadStatus::Closed,
        ]);

        $this->actingAs($user)
            ->getJson('/api/admin/statistics')
            ->assertOk()
            ->assertJsonPath('summary.total', 5)
            ->assertJsonPath('summary.pending', 1)
            ->assertJsonPath('summary.contacted', 1)
            ->assertJsonPath('summary.completed', 2)
            ->assertJsonPath('summary.closed', 1)
            ->assertJsonPath('summary.completed_count', 2)
            ->assertJsonPath('summary.total_revenue', 325.5)
            ->assertJsonCount(2, 'completed_leads')
            ->assertJsonPath('completed_leads.0.id', $latestCompletedLead->id)
            ->assertJsonPath('completed_leads.0.name', 'سارة')
            ->assertJsonPath('completed_leads.0.service.title', 'صيانة')
            ->assertJsonPath('completed_leads.1.id', $olderCompletedLead->id);
    }
}
