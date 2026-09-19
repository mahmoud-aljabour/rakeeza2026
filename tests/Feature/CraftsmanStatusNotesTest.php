<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CraftsmanStatus;
use App\Models\Craftsman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CraftsmanStatusNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_craftsman_status_with_a_note(): void
    {
        $user = User::factory()->create();
        $craftsman = Craftsman::query()->create([
            'name' => 'أحمد',
            'phone' => '0598855299',
            'city' => 'رام الله',
            'specialty' => ['نجارة'],
            'experience_years' => 8,
            'has_tools' => true,
            'bio' => 'حرفي متخصص',
            'status' => CraftsmanStatus::Pending,
        ]);

        $this->actingAs($user)
            ->patchJson("/api/admin/craftsmen/{$craftsman->id}", [
                'status' => 'reviewing',
                'note' => 'تم التواصل وطلب صور إضافية.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'reviewing')
            ->assertJsonPath('notes.0.status', 'reviewing')
            ->assertJsonPath('notes.0.note', 'تم التواصل وطلب صور إضافية.');

        $this->assertDatabaseHas('craftsman_notes', [
            'craftsman_id' => $craftsman->id,
            'status' => 'reviewing',
            'note' => 'تم التواصل وطلب صور إضافية.',
            'user_id' => $user->id,
        ]);
    }

    public function test_craftsmen_list_includes_status_notes(): void
    {
        $user = User::factory()->create();
        $craftsman = Craftsman::query()->create([
            'name' => 'خالد',
            'phone' => '0597111111',
            'city' => 'نابلس',
            'specialty' => ['دهان'],
            'status' => CraftsmanStatus::Reviewing,
        ]);

        $this->actingAs($user)
            ->patchJson("/api/admin/craftsmen/{$craftsman->id}", [
                'status' => 'accepted',
                'note' => 'تم اعتماد الطلب.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'accepted');

        $this->actingAs($user)
            ->getJson('/api/admin/craftsmen')
            ->assertOk()
            ->assertJsonPath('0.id', $craftsman->id)
            ->assertJsonPath('0.status', 'accepted')
            ->assertJsonPath('0.notes.0.note', 'تم اعتماد الطلب.');
    }

    public function test_note_longer_than_limit_is_rejected_without_changing_status(): void
    {
        $user = User::factory()->create();
        $craftsman = Craftsman::query()->create([
            'name' => 'محمود',
            'phone' => '0597222222',
            'city' => 'الخليل',
            'specialty' => ['بلاط'],
            'status' => CraftsmanStatus::Pending,
        ]);

        $this->actingAs($user)
            ->patchJson("/api/admin/craftsmen/{$craftsman->id}", [
                'status' => 'rejected',
                'note' => str_repeat('a', 2001),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('note');

        $this->assertDatabaseHas('craftsmen', [
            'id' => $craftsman->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('craftsman_notes', 0);
    }
}
