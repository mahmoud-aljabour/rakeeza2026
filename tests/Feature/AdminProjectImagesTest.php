<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AdminProjectImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_project_with_image_url(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/api/admin/projects', [
                'title' => 'عمل برابط',
                'details' => 'تفاصيل العمل',
                'sync_images' => '1',
                'items' => [
                    [
                        'source' => 'url',
                        'url' => 'https://example.com/project.jpg',
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('title', 'عمل برابط')
            ->assertJsonPath('image_path', 'https://example.com/project.jpg')
            ->assertJsonPath('image_url', 'https://example.com/project.jpg')
            ->assertJsonPath('image_urls.0', 'https://example.com/project.jpg');
    }

    public function test_admin_can_create_a_project_with_multiple_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('second.jpg');

        $this->actingAs($user)
            ->post('/api/admin/projects', [
                'title' => 'عمل بعدة صور',
                'sync_images' => '1',
                'items' => [
                    [
                        'source' => 'url',
                        'url' => 'https://example.com/cover.jpg',
                    ],
                    [
                        'source' => 'file',
                        'image' => $file,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('image_path', 'https://example.com/cover.jpg')
            ->assertJsonCount(2, 'image_urls');
    }

    public function test_image_url_must_be_http_or_local_images_path(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/admin/projects', [
                'title' => 'عمل برابط خاطئ',
                'items' => [
                    [
                        'source' => 'url',
                        'url' => 'javascript:alert(1)',
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.url']);
    }

    public function test_admin_can_remove_all_project_images(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create([
            'title' => 'عمل قائم',
            'image_path' => 'https://example.com/old.jpg',
            'image_paths' => ['https://example.com/old.jpg'],
            'order_column' => 1,
        ]);

        $this->actingAs($user)
            ->post("/api/admin/projects/{$project->id}", [
                'title' => 'عمل قائم',
                'sync_images' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('image_path', null)
            ->assertJsonPath('image_urls', []);
    }

    public function test_admin_projects_index_is_paginated_without_full_image_urls(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 9) as $index) {
            Project::query()->create([
                'title' => "عمل {$index}",
                'image_path' => "https://example.com/project-{$index}.jpg",
                'image_paths' => ["https://example.com/project-{$index}.jpg"],
                'order_column' => $index,
            ]);
        }

        $this->actingAs($user)
            ->getJson('/api/admin/projects?per_page=8')
            ->assertOk()
            ->assertJsonPath('per_page', 8)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 9)
            ->assertJsonCount(8, 'data')
            ->assertJsonPath('data.0.images_count', 1);
    }

    public function test_admin_can_create_a_project_linked_to_a_service(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'title' => 'خدمات النظافة',
        ]);

        $this->actingAs($user)
            ->post('/api/admin/projects', [
                'title' => 'عمل مرتبط',
                'service_id' => $service->id,
                'sync_images' => '1',
                'items' => [
                    [
                        'source' => 'url',
                        'url' => 'https://example.com/project.jpg',
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('title', 'عمل مرتبط')
            ->assertJsonPath('service_id', $service->id)
            ->assertJsonPath('service.title', 'خدمات النظافة');
    }

    public function test_admin_can_unlink_a_project_from_a_service(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $project = Project::factory()->forService($service)->create([
            'title' => 'عمل قائم',
        ]);

        $this->actingAs($user)
            ->post("/api/admin/projects/{$project->id}", [
                'title' => 'عمل قائم',
                'service_id' => '',
            ])
            ->assertOk()
            ->assertJsonPath('service_id', null);
    }

    public function test_invalid_service_id_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/admin/projects', [
                'title' => 'عمل برابط خدمة خاطئ',
                'service_id' => 999,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['service_id']);
    }
}
