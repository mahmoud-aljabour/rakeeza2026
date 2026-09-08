<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CraftsmanStatus;
use App\Models\Craftsman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CraftsmanWordExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_export_craftsmen(): void
    {
        $this->get('/api/admin/craftsmen/export')->assertUnauthorized();
    }

    public function test_admin_can_export_craftsmen_as_word(): void
    {
        $user = User::factory()->create();
        Craftsman::query()->create([
            'name' => 'أحمد النجار',
            'phone' => '0597000000',
            'city' => 'غزة',
            'specialty' => 'نجارة',
            'experience_years' => 5,
            'has_tools' => true,
            'bio' => 'حرفي متخصص',
            'status' => CraftsmanStatus::Pending,
        ]);

        $this->actingAs($user)
            ->get('/api/admin/craftsmen/export')
            ->assertOk()
            ->assertHeader('content-type', 'application/msword; charset=UTF-8')
            ->assertSee('طلبات تسجيل الحرفيين', false)
            ->assertSee('أحمد النجار', false)
            ->assertSee('نجارة', false);
    }

    public function test_admin_can_export_a_single_craftsman(): void
    {
        $user = User::factory()->create();
        $craftsman = Craftsman::query()->create([
            'name' => 'سارة حداد',
            'phone' => '0597111111',
            'city' => 'رام الله',
            'specialty' => 'حدادة',
            'experience_years' => 3,
            'has_tools' => false,
            'status' => CraftsmanStatus::Reviewing,
        ]);

        $this->actingAs($user)
            ->get("/api/admin/craftsmen/{$craftsman->id}/export")
            ->assertOk()
            ->assertSee('طلب تسجيل حرفي', false)
            ->assertSee('سارة حداد', false)
            ->assertSee('قيد المراجعة', false);
    }
}
