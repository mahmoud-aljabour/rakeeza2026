<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LeadStatusNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_lead_status_with_a_note(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $lead = Lead::query()->create([
            'name' => 'عبدالله',
            'phone' => '0598855299',
            'email' => 'test@example.com',
            'service_id' => $service->id,
            'message' => 'أحتاج خدمة',
            'status' => LeadStatus::Pending,
        ]);

        $this->actingAs($user)
            ->patchJson("/api/admin/leads/{$lead->id}", [
                'status' => 'contacted',
                'note' => 'تم الاتصال بالعميل واتفقنا على موعد.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'contacted')
            ->assertJsonPath('notes.0.status', 'contacted')
            ->assertJsonPath('notes.0.note', 'تم الاتصال بالعميل واتفقنا على موعد.');

        $this->assertDatabaseHas('lead_notes', [
            'lead_id' => $lead->id,
            'status' => 'contacted',
            'note' => 'تم الاتصال بالعميل واتفقنا على موعد.',
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_can_mark_lead_as_completed_and_list_includes_notes(): void
    {
        $user = User::factory()->create();
        $lead = Lead::query()->create([
            'name' => 'سارة',
            'phone' => '0597111111',
            'status' => LeadStatus::Contacted,
        ]);

        $this->actingAs($user)
            ->patchJson("/api/admin/leads/{$lead->id}", [
                'status' => 'completed',
                'completed_price' => '275.50',
                'note' => 'تم إنجاز الطلب.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('completed_price', '275.50');

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => LeadStatus::Completed->value,
            'completed_price' => '275.50',
        ]);
        $this->assertNotNull($lead->refresh()->completed_at);

        $this->actingAs($user)
            ->getJson('/api/admin/leads')
            ->assertOk()
            ->assertJsonPath('data.0.id', $lead->id)
            ->assertJsonPath('data.0.status', 'completed')
            ->assertJsonPath('data.0.notes.0.note', 'تم إنجاز الطلب.');
    }

    public function test_completed_status_requires_a_price(): void
    {
        $user = User::factory()->create();
        $lead = Lead::query()->create([
            'name' => 'سارة',
            'phone' => '0597111111',
            'status' => LeadStatus::Contacted,
        ]);

        $this->actingAs($user)
            ->patchJson("/api/admin/leads/{$lead->id}", [
                'status' => 'completed',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('completed_price');

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => LeadStatus::Contacted->value,
            'completed_price' => null,
        ]);
    }
}
