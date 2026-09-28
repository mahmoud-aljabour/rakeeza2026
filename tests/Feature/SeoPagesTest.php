<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
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
            $url = url('/services/'.$slug);

            $response = $this->get('/services/'.$slug)
                ->assertOk()
                ->assertSee('<title>'.$service->seo_title.'</title>', false)
                ->assertSee('<meta name="description" content="'.$service->meta_description.'">', false)
                ->assertSee('<link rel="canonical" href="'.$url.'">', false)
                ->assertSee('<meta property="og:title" content="'.$service->seo_title.'">', false)
                ->assertSee('<meta property="og:description" content="'.$service->meta_description.'">', false)
                ->assertSee('<meta property="og:url" content="'.$url.'">', false)
                ->assertSee('<meta property="og:image" content="https://example.com/service.jpg">', false)
                ->assertSee('<h1>'.$heading.'</h1>', false)
                ->assertSee('alt="'.$service->imageAlt().'"', false)
                ->assertSee('"@type":"Service"', false)
                ->assertSee('"@type":"BreadcrumbList"', false)
                ->assertSee('أرسل طلبك من هنا', false)
                ->assertSee('id="contact"', false)
                ->assertSee(route('landing'), false);

            $this->assertSame(1, substr_count($response->getContent(), '<h1>'));

            if ($slug === 'cleaning') {
                $response
                    ->assertSee('<h2>مراحل التنظيف</h2>', false)
                    ->assertSee('<h3>ماذا تذكر في الطلب</h3>', false);
            }
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
            ->assertSee('<title>ركيزة (Rakeeza) | خدمات النظافة، الصيانة، وإزالة الركام في غزة</title>', false)
            ->assertSee('<meta name="description" content="منصة ركيزة تقدم خدمات ميدانية متكاملة في قطاع غزة تشمل تنظيف المنازل والمنشآت، إزالة الركام، الصيانة العامة، وتجهيز المواقع للمؤسسات والأفراد. اطلب خدمتك الآن.">', false)
            ->assertSee('<link rel="canonical" href="'.route('landing').'">', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:description"', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('"@type":"LocalBusiness"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"@type":"PostalAddress"', false)
            ->assertSee('منصة ركيزة - Rakeeza', false)
            ->assertSee('قطاع غزة، فلسطين', false)
            ->assertSee('قطاع غزة', false)
            ->assertSee('0597199099', false)
            ->assertSee('INFO@RAKEEZA-PS.COM', false);

        foreach (['cleaning', 'debris-removal', 'general-maintenance', 'health-safety', 'interior-design', 'organizations'] as $slug) {
            $service = Service::query()->where('slug', $slug)->firstOrFail();
            $response->assertSee(route('services.show', $service), false);
            $response->assertSee('alt="'.$service->imageAlt().'"', false);
        }
    }

    public function test_public_json_ld_is_valid_and_private_pages_omit_it(): void
    {
        Service::factory()->create([
            'title' => 'تنظيف "منازل"',
            'slug' => 'quoted-cleaning',
            'seo_title' => 'تنظيف </script> المنازل في غزة',
            'meta_description' => 'وصف يحتوي على "اقتباس" وشرطة / لمحركات البحث في غزة.',
        ]);

        $homeHtml = $this->get(route('landing'))->getContent();
        $this->assertSame(1, substr_count($homeHtml, 'application/ld+json'));

        $home = $this->schema($homeHtml);
        $graph = $home['@graph'];

        $this->assertSame('https://schema.org', $home['@context']);
        $this->assertSame('LocalBusiness', $graph[0]['@type']);
        $this->assertSame('منصة ركيزة - Rakeeza', $graph[0]['name']);
        $this->assertSame('https://rakeeza-ps.com', $graph[0]['url']);
        $this->assertSame('https://rakeeza-ps.com/images/logo.png', $graph[0]['logo']);
        $this->assertSame('منصة ركيزة تقدم خدمات ميدانية متكاملة في قطاع غزة تشمل تنظيف المنازل والمنشآت، إزالة الركام، الصيانة العامة، وتجهيز المواقع للمؤسسات والأفراد.', $graph[0]['description']);
        $this->assertSame('Gaza', $graph[0]['address']['addressLocality']);
        $this->assertSame('Gaza Strip', $graph[0]['address']['addressRegion']);
        $this->assertSame('PS', $graph[0]['address']['addressCountry']);
        $this->assertSame('Place', $graph[0]['areaServed']['@type']);
        $this->assertSame('قطاع غزة، فلسطين', $graph[0]['areaServed']['name']);
        $this->assertSame('0597199099', $graph[0]['telephone']);
        $this->assertSame('INFO@RAKEEZA-PS.COM', $graph[0]['email']);
        $this->assertSame('https://wa.me/970597199099', $graph[0]['sameAs'][0]);
        $this->assertSame('WebSite', $graph[1]['@type']);
        $this->assertSame('https://rakeeza-ps.com', $graph[1]['url']);

        $page = $this->get('/services/quoted-cleaning')->getContent();
        $this->assertSame(1, substr_count($page, 'application/ld+json'));
        $serviceSchema = $this->schema($page);
        $serviceGraph = $serviceSchema['@graph'];

        $this->assertSame('Service', $serviceGraph[0]['@type']);
        $this->assertSame('تنظيف </script> المنازل في غزة', $serviceGraph[0]['name']);
        $this->assertSame('وصف يحتوي على "اقتباس" وشرطة / لمحركات البحث في غزة.', $serviceGraph[0]['description']);
        $this->assertSame(url('/services/quoted-cleaning'), $serviceGraph[0]['url']);
        $this->assertSame('منصة ركيزة - Rakeeza', $serviceGraph[0]['provider']['name']);
        $this->assertSame('https://rakeeza-ps.com', $serviceGraph[0]['provider']['url']);
        $this->assertSame('BreadcrumbList', $serviceGraph[1]['@type']);
        $this->assertSame(1, $serviceGraph[1]['itemListElement'][0]['position']);
        $this->assertSame('الرئيسية', $serviceGraph[1]['itemListElement'][0]['name']);
        $this->assertSame('https://rakeeza-ps.com', $serviceGraph[1]['itemListElement'][0]['item']);
        $this->assertSame(2, $serviceGraph[1]['itemListElement'][1]['position']);
        $this->assertSame('الخدمات', $serviceGraph[1]['itemListElement'][1]['name']);
        $this->assertSame('https://rakeeza-ps.com/#services', $serviceGraph[1]['itemListElement'][1]['item']);
        $this->assertSame(3, $serviceGraph[1]['itemListElement'][2]['position']);
        $this->assertSame('تنظيف "منازل"', $serviceGraph[1]['itemListElement'][2]['name']);
        $this->assertSame(url('/services/quoted-cleaning'), $serviceGraph[1]['itemListElement'][2]['item']);
        $this->assertStringNotContainsString('</script>', $this->jsonLd($page));
    }

    public function test_service_page_links_to_other_services_with_breadcrumbs(): void
    {
        $this->seedCatalogServices();
        $hidden = Service::factory()->inactive()->create(['title' => 'خدمة مخفية', 'slug' => 'hidden-service']);

        $response = $this->get('/services/cleaning');

        $response
            ->assertSee('<li><a href="'.route('landing').'">الرئيسية</a></li>', false)
            ->assertSee('<li><a href="'.url('/#services').'">الخدمات</a></li>', false)
            ->assertSee('<li><span aria-current="page">خدمات النظافة</span></li>', false)
            ->assertSee('<h2 id="service-related-title">خدمات ميدانية أخرى نقدمها في غزة</h2>', false)
            ->assertSee('<h3><a href="'.route('services.show', 'debris-removal').'">خدمات إزالة الركام</a></h3>', false)
            ->assertSee('<h3><a href="'.route('services.show', 'organizations').'">خدمات للمؤسسات والمنظمات</a></h3>', false)
            ->assertSee('<p>وصف قصير للبطاقة.</p>', false)
            ->assertDontSee('<h3><a href="'.route('services.show', 'cleaning').'">', false)
            ->assertDontSee(route('services.show', $hidden), false);

        $related = $this->between($response->getContent(), 'id="service-related-title"', '</section>');
        $this->assertSame(5, substr_count($related, '<h3>'));
    }

    public function test_footer_links_to_every_active_service_on_each_public_page(): void
    {
        $this->seedCatalogServices();

        foreach ([route('landing'), route('privacy'), route('craftsman.create'), route('services.show', 'cleaning')] as $url) {
            $footer = $this->between($this->get($url)->getContent(), '<footer id="site-footer"', '</footer>');

            foreach (['cleaning', 'debris-removal', 'general-maintenance', 'health-safety', 'interior-design', 'organizations'] as $slug) {
                $this->assertStringContainsString('href="'.route('services.show', $slug).'"', $footer, $url);
            }
        }
    }

    public function test_public_images_have_descriptive_alt_text_and_loading_hints(): void
    {
        $service = Service::factory()->create([
            'title' => 'خدمات النظافة',
            'slug' => 'cleaning',
        ]);
        Service::factory()->create(['title' => 'خدمات إزالة الركام', 'slug' => 'debris-removal']);
        Project::factory()->forService($service)->create([
            'title' => 'تنظيف منشأة',
            'image_path' => 'https://example.com/a.jpg',
            'image_paths' => ['https://example.com/a.jpg', 'https://example.com/b.jpg'],
        ]);

        $servicePage = $this->get('/services/cleaning')->getContent();
        $homePage = $this->get(route('landing'))->getContent();

        $this->assertStringContainsString('alt="خدمات النظافة في قطاع غزة - منصة ركيزة" fetchpriority="high"', $servicePage);
        $this->assertStringContainsString('alt="تنظيف منشأة - ركيزة" loading="lazy" decoding="async" data-work-image', $servicePage);
        $this->assertStringContainsString('alt="تنظيف منشأة - صورة 2 - ركيزة" loading="lazy" decoding="async"', $servicePage);
        $this->assertStringContainsString('alt="خدمات إزالة الركام في قطاع غزة - منصة ركيزة" loading="lazy" decoding="async"', $servicePage);
        $this->assertStringContainsString('alt="تنظيف منشأة - ركيزة" loading="lazy" decoding="async" data-project-image', $homePage);
        $this->assertStringContainsString('fetchpriority="high"', $homePage);

        foreach (['service' => $servicePage, 'home' => $homePage] as $label => $html) {
            preg_match_all('/<img\b[^>]*>/u', $html, $images);

            $this->assertNotEmpty($images[0], $label);

            foreach ($images[0] as $image) {
                $this->assertMatchesRegularExpression('/\salt="[^"]+"/u', $image, $label.': '.$image);

                if (! str_contains($image, 'fetchpriority="high"') && ! str_contains($image, 'width="85"')) {
                    $this->assertStringContainsString('loading="lazy"', $image, $label.': '.$image);
                    $this->assertStringContainsString('decoding="async"', $image, $label.': '.$image);
                }
            }
        }
    }

    public function test_project_without_a_title_falls_back_to_the_service_name(): void
    {
        $project = new Project(['title' => '']);

        $this->assertSame('مشروع خدمات النظافة - ركيزة', $project->imageAlt('خدمات النظافة'));
    }

    public function test_long_service_text_renders_headings_lists_and_paragraphs(): void
    {
        Service::factory()->create([
            'slug' => 'structured-body',
            'body' => "مقدمة الخدمة.\n\n## ما نقدمه\n- تنظيف الأرضيات\n- غسيل النوافذ <b>\n\n### التفاصيل\nفقرة أخيرة.",
        ]);

        $this->get('/services/structured-body')
            ->assertSee('<p>مقدمة الخدمة.</p>', false)
            ->assertSee('<h2>ما نقدمه</h2>', false)
            ->assertSee('<li>تنظيف الأرضيات</li>', false)
            ->assertSee('<li>غسيل النوافذ &lt;b&gt;</li>', false)
            ->assertSee('<h3>التفاصيل</h3>', false)
            ->assertSee('<p>فقرة أخيرة.</p>', false);
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

    public function test_robots_file_allows_the_site_without_listing_private_paths(): void
    {
        $this->assertSame(
            "User-agent: *\nAllow: /\nSitemap: https://rakeeza-ps.com/sitemap.xml\n",
            str_replace("\r\n", "\n", (string) file_get_contents(public_path('robots.txt'))),
        );
    }

    public function test_admin_and_auth_responses_are_not_indexed(): void
    {
        foreach (['/admin', '/admin/login', '/admin/services'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
                ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
                ->assertDontSee('application/ld+json', false);
        }

        $this->postJson('/login')
            ->assertUnprocessable()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->getJson('/api/admin/stats')
            ->assertUnauthorized()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->actingAs(User::factory()->create())
            ->getJson('/api/admin/stats')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->withHeader('Origin', 'https://example.org')
            ->getJson('/api/admin/stats')
            ->assertForbidden()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_public_pages_stay_indexable(): void
    {
        $service = Service::factory()->create(['slug' => 'cleaning']);

        foreach ([route('landing'), route('services.show', $service), route('privacy'), route('craftsman.create'), route('sitemap')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertHeaderMissing('X-Robots-Tag')
                ->assertDontSee('noindex', false);
        }
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

    public function test_service_page_uses_the_service_name_when_the_search_title_is_empty(): void
    {
        Service::factory()->create([
            'title' => 'تنظيف المنازل',
            'slug' => 'home-cleaning',
            'seo_title' => null,
            'meta_description' => null,
            'description' => 'تنظيف منازل في قطاع غزة عند غياب وصف الميتا.',
        ]);

        $this->get('/services/home-cleaning')
            ->assertOk()
            ->assertSee('<title>تنظيف المنازل | ركيزة</title>', false)
            ->assertSee('<meta name="description" content="تنظيف منازل في قطاع غزة عند غياب وصف الميتا.">', false)
            ->assertSee('<meta property="og:title" content="تنظيف المنازل | ركيزة">', false);
    }

    public function test_craftsman_page_exposes_search_and_share_tags(): void
    {
        $this->get(route('craftsman.create'))
            ->assertOk()
            ->assertSee('name="description"', false)
            ->assertSee('<link rel="canonical" href="'.route('craftsman.create').'">', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:image"', false);
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

    /**
     * @return array<string, mixed>
     */
    private function schema(string $html): array
    {
        $decoded = json_decode($this->jsonLd($html), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($decoded);

        return $decoded;
    }

    private function between(string $html, string $start, string $end): string
    {
        $from = strpos($html, $start);

        $this->assertNotFalse($from, $start);

        $to = strpos($html, $end, $from);

        $this->assertNotFalse($to, $end);

        return substr($html, $from, $to - $from);
    }

    private function jsonLd(string $html): string
    {
        $matched = preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

        $this->assertSame(1, $matched);

        return $matches[1];
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
