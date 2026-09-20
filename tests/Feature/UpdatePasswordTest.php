<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_update_password(): void
    {
        $this->putJson('/api/admin/password', [
            'current_password' => 'password',
            'password' => 'Secret1!pass',
            'password_confirmation' => 'Secret1!pass',
        ])->assertUnauthorized();
    }

    public function test_admin_can_update_password_with_valid_payload(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->putJson('/api/admin/password', [
                'current_password' => 'password',
                'password' => 'Secret1!pass',
                'password_confirmation' => 'Secret1!pass',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'تم تحديث كلمة المرور بنجاح.');

        $user->refresh();

        $this->assertTrue(Hash::check('Secret1!pass', $user->password));
        $this->assertFalse(Hash::check('password', $user->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->putJson('/api/admin/password', [
                'current_password' => 'wrong-password',
                'password' => 'Secret1!pass',
                'password_confirmation' => 'Secret1!pass',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_weak_new_password_is_rejected(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->putJson('/api/admin/password', [
                'current_password' => 'password',
                'password' => 'weak',
                'password_confirmation' => 'weak',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->putJson('/api/admin/password', [
                'current_password' => 'password',
                'password' => 'Secret1!pass',
                'password_confirmation' => 'Secret1!other',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
