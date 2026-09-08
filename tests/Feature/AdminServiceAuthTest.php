<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminServiceAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_a_service(): void
    {
        $this->postJson('/api/admin/services', [
            'title' => 'خدمة تجريبية',
        ])->assertUnauthorized();
    }

    public function test_logged_in_admin_can_create_a_service(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/admin/services', [
                'title' => 'خدمة تجريبية',
                'description' => 'وصف الخدمة',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('title', 'خدمة تجريبية');
    }

    public function test_logged_in_admin_can_create_a_service_with_image_url(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/admin/services', [
                'title' => 'خدمة برابط',
                'image_url' => 'https://example.com/service.jpg',
            ])
            ->assertCreated()
            ->assertJsonPath('image_path', 'https://example.com/service.jpg')
            ->assertJsonPath('image_url', 'https://example.com/service.jpg');
    }

    public function test_image_url_must_be_http_or_local_images_path(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/admin/services', [
                'title' => 'خدمة برابط خاطئ',
                'image_url' => 'javascript:alert(1)',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image_url']);
    }

    public function test_admin_services_index_is_paginated(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 10) as $index) {
            Service::query()->create([
                'title' => "خدمة {$index}",
                'is_active' => true,
            ]);
        }

        $this->actingAs($user)
            ->getJson('/api/admin/services?per_page=9')
            ->assertOk()
            ->assertJsonPath('per_page', 9)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 10)
            ->assertJsonCount(9, 'data');
    }

    public function test_admin_can_list_all_services_without_pagination(): void
    {
        $user = User::factory()->create();

        Service::factory()->count(3)->create();

        $this->actingAs($user)
            ->getJson('/api/admin/services?all=1')
            ->assertOk()
            ->assertJsonCount(3);
    }
}
