<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

final class SeoFinalAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_pages_render_the_arabic_404_with_links_out(): void
    {
        Service::factory()->create(['title' => 'خدمات النظافة', 'slug' => 'cleaning']);
        Service::factory()->create(['title' => 'خدمات إزالة الركام', 'slug' => 'debris-removal']);

        foreach (['/no-such-page', '/services/missing-service'] as $path) {
            $html = $this->get($path)
                ->assertNotFound()
                ->assertSee('<meta name="robots" content="noindex, follow">', false)
                ->assertSee('<title>الصفحة غير موجودة | ركيزة</title>', false)
                ->assertSee('<h1>لم نجد الصفحة التي تبحث عنها</h1>', false)
                ->assertSee('href="'.route('landing').'" class="btn-primary"', false)
                ->assertSee('<h3><a href="'.route('services.show', 'cleaning').'">خدمات النظافة</a></h3>', false)
                ->assertSee('<h3><a href="'.route('services.show', 'debris-removal').'">خدمات إزالة الركام</a></h3>', false)
                ->assertDontSee('rel="canonical"', false)
                ->getContent();

            $this->assertSame(1, substr_count($html, '<h1>'), $path);
        }
    }

    public function test_sitemap_lastmod_follows_service_changes(): void
    {
        $this->travelTo('2026-01-10 09:00:00');
        $service = Service::factory()->create(['slug' => 'cleaning']);
        $removed = Service::factory()->create(['slug' => 'old-service']);

        $this->get(route('sitemap'))
            ->assertSee('<lastmod>2026-01-10T09:00:00+00:00</lastmod>', false)
            ->assertSee(route('services.show', $removed), false);

        $this->travelTo('2026-02-20 14:30:00');
        $service->update(['title' => 'خدمات النظافة المحدثة']);
        $removed->delete();

        $entries = $this->sitemapEntries($this->get(route('sitemap'))->getContent());

        $this->assertSame('2026-02-20T14:30:00+00:00', $entries[route('services.show', 'cleaning')]);
        $this->assertSame('2026-02-20T14:30:00+00:00', $entries[route('landing')]);
        $this->assertArrayHasKey(route('craftsman.create'), $entries);
        $this->assertArrayNotHasKey(route('services.show', 'old-service'), $entries);
    }

    public function test_production_urls_use_the_https_app_url(): void
    {
        config(['app.url' => 'https://rakeeza-ps.com']);
        $this->app->detectEnvironment(static fn (): string => 'production');

        (new AppServiceProvider($this->app))->configureUrls();

        $this->assertSame('https://rakeeza-ps.com/services/cleaning', route('services.show', 'cleaning'));
        $this->assertSame('https://rakeeza-ps.com/sitemap.xml', route('sitemap'));

        URL::forceRootUrl(null);
        URL::forceScheme(null);
    }

    public function test_local_urls_are_left_alone(): void
    {
        config(['app.url' => 'https://rakeeza-ps.com']);

        (new AppServiceProvider($this->app))->configureUrls();

        $this->assertStringStartsWith('http://', route('landing'));
    }

    public function test_htaccess_redirects_www_and_http_to_the_canonical_https_host(): void
    {
        $htaccess = str_replace("\r\n", "\n", (string) file_get_contents(public_path('.htaccess')));

        $this->assertStringContainsString("RewriteCond %{HTTP_HOST} ^www\\.rakeeza-ps\\.com$ [NC]\n    RewriteRule ^ https://rakeeza-ps.com%{REQUEST_URI} [L,R=301]", $htaccess);
        $this->assertStringContainsString("RewriteCond %{HTTPS} !=on\n    RewriteCond %{HTTP:X-Forwarded-Proto} !=https\n    RewriteRule ^ https://rakeeza-ps.com%{REQUEST_URI} [L,R=301]", $htaccess);
        $this->assertLessThan(strpos($htaccess, 'RewriteRule ^ index.php'), strpos($htaccess, 'R=301]'));
    }

    public function test_public_pages_have_exactly_one_title_description_and_canonical(): void
    {
        $service = Service::factory()->create(['slug' => 'cleaning']);

        foreach ([route('landing'), route('services.show', $service), route('privacy'), route('craftsman.create')] as $url) {
            $head = strstr($this->get($url)->assertOk()->getContent(), '</head>', true);

            $this->assertSame(1, substr_count($head, '<title>'), $url);
            $this->assertSame(1, substr_count($head, '<meta name="description"'), $url);
            $this->assertSame(1, substr_count($head, 'rel="canonical"'), $url);
            $this->assertSame(1, substr_count($head, 'property="og:title"'), $url);
            $this->assertLessThanOrEqual(1, substr_count($head, 'application/ld+json'), $url);
        }
    }

    /**
     * @return array<string, string|null>
     */
    private function sitemapEntries(string $xml): array
    {
        $document = simplexml_load_string($xml);

        $this->assertNotFalse($document);

        $entries = [];

        foreach ($document->url as $url) {
            $entries[(string) $url->loc] = isset($url->lastmod) ? (string) $url->lastmod : null;
        }

        return $entries;
    }
}
