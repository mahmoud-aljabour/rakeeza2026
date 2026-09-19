<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CraftsmanStatus;
use App\Enums\LeadStatus;
use App\Models\Craftsman;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_dashboard_stats(): void
    {
        $this->getJson('/api/admin/stats')->assertUnauthorized();
    }

    public function test_dashboard_includes_the_latest_leads_and_craftsmen(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'title' => 'سباكة',
        ]);

        Lead::query()->create([
            'name' => 'طلب قديم',
            'phone' => '0591000000',
            'service_id' => $service->id,
            'message' => 'طلب سابق',
            'status' => LeadStatus::Closed,
        ]);

        $latestLead = Lead::query()->create([
            'name' => 'أحمد علي',
            'phone' => '0591234567',
            'service_id' => $service->id,
            'message' => 'أحتاج سباكة',
            'status' => LeadStatus::Pending,
        ]);

        Craftsman::query()->create([
            'name' => 'حرفي قديم',
            'phone' => '0592000000',
            'city' => 'نابلس',
            'specialty' => 'كهرباء',
            'status' => CraftsmanStatus::Rejected,
        ]);

        $latestCraftsman = Craftsman::query()->create([
            'name' => 'سارة حداد',
            'phone' => '0597111111',
            'city' => 'رام الله',
            'specialty' => 'حدادة',
            'status' => CraftsmanStatus::Pending,
        ]);

        $this->actingAs($user)
            ->getJson('/api/admin/stats')
            ->assertOk()
            ->assertJsonPath('recent_leads.0.id', $latestLead->id)
            ->assertJsonPath('recent_leads.0.name', 'أحمد علي')
            ->assertJsonPath('recent_leads.0.service.title', 'سباكة')
            ->assertJsonPath('recent_craftsmen.0.id', $latestCraftsman->id)
            ->assertJsonPath('recent_craftsmen.0.name', 'سارة حداد')
            ->assertJsonPath('recent_craftsmen.0.specialty', 'حدادة')
            ->assertJsonCount(2, 'recent_leads')
            ->assertJsonCount(2, 'recent_craftsmen');
    }

    public function test_dashboard_limits_recent_lists_to_five_items(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        foreach (range(1, 7) as $index) {
            Lead::query()->create([
                'name' => "عميل {$index}",
                'phone' => '059100000'.$index,
                'service_id' => $service->id,
                'status' => LeadStatus::Pending,
            ]);

            Craftsman::query()->create([
                'name' => "حرفي {$index}",
                'phone' => '059200000'.$index,
                'city' => 'غزة',
                'specialty' => 'نجارة',
                'status' => CraftsmanStatus::Pending,
            ]);
        }

        $this->actingAs($user)
            ->getJson('/api/admin/stats')
            ->assertOk()
            ->assertJsonCount(5, 'recent_leads')
            ->assertJsonCount(5, 'recent_craftsmen');
    }
}
