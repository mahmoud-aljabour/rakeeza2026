<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_copy_stays_within_search_length_limits(): void
    {
        /** @var array<string, array<string, string>> $pages */
        $pages = require database_path('data/service_pages.php');
        $arabic = require lang_path('ar/site.php');
        $english = require lang_path('en/site.php');

        $this->assertCount(6, $pages);
        $this->assertGreaterThanOrEqual(150, $this->length($arabic['meta']['landing_description']));
        $this->assertLessThanOrEqual(160, $this->length($arabic['meta']['landing_description']));
        $this->assertGreaterThanOrEqual(150, $this->length($english['meta']['landing_description']));
        $this->assertLessThanOrEqual(160, $this->length($english['meta']['landing_description']));

        foreach ($pages as $title => $page) {
            $this->assertGreaterThanOrEqual(300, $this->words($page['body']), $title);
            $this->assertLessThanOrEqual(500, $this->words($page['body']), $title);
            $this->assertGreaterThanOrEqual(300, $this->words($page['body_en']), $title);
            $this->assertLessThanOrEqual(500, $this->words($page['body_en']), $title);
            $this->assertGreaterThanOrEqual(150, $this->length($page['meta_description']), $title);
            $this->assertLessThanOrEqual(160, $this->length($page['meta_description']), $title);
            $this->assertGreaterThanOrEqual(150, $this->length($page['meta_description_en']), $title);
            $this->assertLessThanOrEqual(160, $this->length($page['meta_description_en']), $title);
        }
    }

    public function test_each_core_service_has_its_own_page(): void
    {
        $this->seedCatalogServices();

        $expected = [
            'cleaning' => 'خدمات النظافة في قطاع غزة',
            'debris-removal' => 'خدمات إزالة الركام في قطاع غزة',
            'general-maintenance' => 'خدمات الصيانة العامة في قطاع غزة',
            'health-safety' => 'خدمات الصحة والسلامة في قطاع غزة',
            'interior-design' => 'التصميم الداخلي والديكور في قطاع غزة',
            'organizations' => 'خدمات للمؤسسات والمنظمات في قطاع غزة',
        ];

        foreach ($expected as $slug => $heading) {
            $service = Service::query()->where('slug', $slug)->firstOrFail();

            $this->get('/services/'.$slug)
                ->assertOk()
                ->assertSee('<title>'.$service->seo_title.'</title>', false)
                ->assertSee('<h1>'.$heading.'</h1>', false)
                ->assertSee('name="description"', false)
                ->assertSee('property="og:title"', false)
                ->assertSee('property="og:image"', false)
                ->assertSee('alt="'.$service->imageAlt().'"', false)
                ->assertSee('"@type":"Service"', false)
                ->assertSee('أرسل طلبك من هنا', false)
                ->assertSee('id="contact"', false)
                ->assertSee(route('landing'), false);
        }

        $cleaning = Service::query()->where('slug', 'cleaning')->firstOrFail();

        $this->get(route('services.show', $cleaning))
            ->assertOk()
            ->assertSee(route('services.show', 'organizations'), false)
            ->assertSee(route('services.show', 'debris-removal'), false);
    }

    public function test_homepage_links_to_every_service_and_exposes_local_business_data(): void
    {
        $this->seedCatalogServices();

        $response = $this->get(route('landing'))
            ->assertOk()
            ->assertSee('ركيزة | خدمات المنازل والمنشآت في غزة', false)
            ->assertSee('name="description"', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('"@type":"LocalBusiness"', false)
            ->assertSee('خدمات منزلية ومنشآت', false)
            ->assertSee('قطاع غزة', false)
            ->assertSee('0597199099', false)
            ->assertSee('INFO@RAKEEZA-PS.COM', false);

        foreach (['cleaning', 'debris-removal', 'general-maintenance', 'health-safety', 'interior-design', 'organizations'] as $slug) {
            $service = Service::query()->where('slug', $slug)->firstOrFail();
            $response->assertSee(route('services.show', $service), false);
            $response->assertSee('alt="'.$service->imageAlt().'"', false);
        }
    }

    public function test_sitemap_lists_the_homepage_and_active_services_only(): void
    {
        $this->seedCatalogServices();
        $hidden = Service::factory()->inactive()->create([
            'slug' => 'hidden-service',
        ]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('landing'), false)
            ->assertSee(route('services.show', 'cleaning'), false)
            ->assertDontSee(route('services.show', $hidden), false);
    }

    public function test_robots_allows_the_full_site_and_points_at_the_sitemap(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('User-agent: *', false)
            ->assertSee('Allow: /', false)
            ->assertSee('Sitemap: '.route('sitemap'), false)
            ->assertDontSee('Disallow: /', false);
    }

    public function test_numeric_service_urls_redirect_to_the_slug(): void
    {
        $service = Service::factory()->create([
            'title' => 'خدمات إزالة الركام',
            'slug' => 'debris-removal',
        ]);

        $this->get('/services/'.$service->id)
            ->assertStatus(301)
            ->assertRedirect(route('services.show', $service));
    }

    public function test_unknown_service_slug_is_not_found(): void
    {
        $this->get('/services/missing-service')->assertNotFound();
    }

    public function test_admin_can_edit_service_search_fields(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'title' => 'خدمات إزالة الركام',
            'slug' => 'debris-removal',
        ]);
        $meta = 'ركيزة تزيل ركام الترميم والهدم في قطاع غزة مع فرز ونقل منظم، وتسلّم موقعاً جاهزاً للعمل. اطلب المعاينة الآن عبر نموذج أرسل طلبك. الرد خلال ساعات العمل.';

        $this->actingAs($user)
            ->putJson('/api/admin/services/'.$service->id, [
                'title' => 'خدمات إزالة الركام',
                'slug' => 'debris-removal',
                'seo_title' => 'خدمات إزالة الركام في غزة | ركيزة',
                'meta_description' => $meta,
            ])
            ->assertOk()
            ->assertJsonPath('seo_title', 'خدمات إزالة الركام في غزة | ركيزة')
            ->assertJsonPath('meta_description', $meta);

        $this->get('/services/debris-removal')
            ->assertOk()
            ->assertSee('<title>خدمات إزالة الركام في غزة | ركيزة</title>', false)
            ->assertSee($meta, false);
    }

    public function test_meta_description_outside_the_snippet_length_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/admin/services', [
                'title' => 'خدمة تجريبية',
                'meta_description' => str_repeat('أ', 161),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['meta_description']);
    }

    private function seedCatalogServices(): void
    {
        /** @var array<string, array<string, string>> $pages */
        $pages = require database_path('data/service_pages.php');

        foreach ($pages as $title => $page) {
            Service::factory()->create([
                'title' => $title,
                'slug' => $page['slug'],
                'description' => 'وصف قصير للبطاقة.',
                'seo_title' => $page['seo_title'],
                'meta_description' => $page['meta_description'],
                'body' => $page['body'],
            ]);
        }
    }

    private function words(string $text): int
    {
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        return is_array($words) ? count($words) : 0;
    }

    private function length(string $text): int
    {
        return mb_strlen($text);
    }
}
