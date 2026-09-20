<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EnsureFirstPartyApiOriginTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_request_without_origin_or_referer_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->withHeaders([
            'Origin' => '',
            'Referer' => '',
        ])->actingAs($user)
            ->getJson('/api/admin/stats')
            ->assertForbidden()
            ->assertJsonPath('message', 'Invalid request origin.');
    }

    public function test_api_request_from_untrusted_origin_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->withHeaders([
            'Origin' => 'https://evil.example',
            'Referer' => '',
        ])->actingAs($user)
            ->getJson('/api/admin/stats')
            ->assertForbidden()
            ->assertJsonPath('message', 'Invalid request origin.');
    }

    public function test_api_request_with_trusted_referer_is_accepted(): void
    {
        $user = User::factory()->create();
        $origin = rtrim((string) config('app.url'), '/');

        $this->withHeaders([
            'Origin' => '',
            'Referer' => $origin.'/admin',
        ])
            ->actingAs($user)
            ->getJson('/api/admin/stats')
            ->assertOk();
    }

    public function test_api_request_with_trusted_origin_is_accepted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/admin/stats')
            ->assertOk();
    }
}
