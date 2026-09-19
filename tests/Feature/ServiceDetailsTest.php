<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ServiceDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_service_card_links_to_details_page(): void
    {
        $service = Service::factory()->create([
            'title' => 'خدمات النظافة',
        ]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('عرض التفاصيل', false)
            ->assertSee(route('services.show', $service), false);
    }

    public function test_guest_can_view_service_details_and_linked_projects(): void
    {
        $service = Service::factory()->create([
            'title' => 'خدمات النظافة',
            'description' => 'وصف خدمة النظافة الكامل.',
        ]);
        $other = Service::factory()->create([
            'title' => 'خدمات أخرى',
        ]);

        Project::factory()->forService($service)->create([
            'title' => 'تنظيف منشأة',
        ]);
        Project::factory()->forService($other)->create([
            'title' => 'مشروع آخر',
        ]);
        Project::factory()->create([
            'title' => 'عمل عام',
        ]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('خدمات النظافة', false)
            ->assertSee('وصف خدمة النظافة الكامل.', false)
            ->assertSee('تفاصيل الخدمة', false)
            ->assertSee('مشاريع تم تنفيذها', false)
            ->assertSee('تنظيف منشأة', false)
            ->assertSee('اطلب سعر عبر واتساب', false)
            ->assertSee('إرسال الطلب', false)
            ->assertSee('fa-envelope', false)
            ->assertDontSee('إرسال عبر واتساب', false)
            ->assertDontSee('data-channel="email"', false)
            ->assertDontSee('data-whatsapp', false)
            ->assertDontSee('مشروع آخر', false)
            ->assertDontSee('عمل عام', false);
    }

    public function test_inactive_service_is_hidden_from_the_public(): void
    {
        $service = Service::factory()->inactive()->create();

        $this->get('/services/'.$service->id)->assertNotFound();
    }

    public function test_service_without_projects_shows_empty_state(): void
    {
        $service = Service::factory()->create();

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('لا توجد مشاريع معروضة لهذه الخدمة بعد.', false);
    }

    public function test_landing_project_with_multiple_images_uses_lightbox_data(): void
    {
        Project::factory()->create([
            'title' => 'عمل بعدة صور',
            'image_path' => 'https://example.com/a.jpg',
            'image_paths' => [
                'https://example.com/a.jpg',
                'https://example.com/b.jpg',
                'https://example.com/c.jpg',
            ],
        ]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('3 صور', false)
            ->assertSee('https://example.com/b.jpg', false)
            ->assertSee('data-lightbox-open', false)
            ->assertDontSee('data-work-prev', false);
    }

    public function test_service_details_show_thumbnails_for_multiple_images(): void
    {
        $service = Service::factory()->create();
        Project::factory()->forService($service)->create([
            'title' => 'تنظيف منشأة',
            'image_path' => 'https://example.com/a.jpg',
            'image_paths' => [
                'https://example.com/a.jpg',
                'https://example.com/b.jpg',
            ],
        ]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('service-work-thumbs', false)
            ->assertSee('https://example.com/b.jpg', false)
            ->assertSee('2 صور', false)
            ->assertSee('data-work-prev', false)
            ->assertSee('data-work-next', false);
    }
}
