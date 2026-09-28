<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PublicPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_query_count_does_not_grow_with_services_or_projects(): void
    {
        $this->seedCatalog(2);
        $fewHome = $this->queriesFor(route('landing'));
        $fewService = $this->queriesFor('/services/service-1');

        $this->seedCatalog(6, 3);
        $manyHome = $this->queriesFor(route('landing'));
        $manyService = $this->queriesFor('/services/service-1');

        $this->assertSame($fewHome, $manyHome);
        $this->assertSame($fewService, $manyService);
    }

    public function test_active_services_are_cached_between_requests(): void
    {
        Service::factory()->count(3)->create();
        $this->get(route('landing'));

        DB::enableQueryLog();
        $this->get(route('landing'));
        $serviceQueries = collect(DB::getQueryLog())
            ->filter(static fn (array $query): bool => str_contains($query['query'], 'from "services"'));
        DB::disableQueryLog();

        $this->assertCount(0, $serviceQueries);
    }

    public function test_saving_or_deleting_a_service_refreshes_the_public_list(): void
    {
        $service = Service::factory()->create(['title' => 'اسم قديم']);
        $this->get(route('landing'))->assertSee('اسم قديم', false);

        $service->update(['title' => 'اسم جديد']);

        $this->get(route('landing'))
            ->assertSee('اسم جديد', false)
            ->assertDontSee('اسم قديم', false);

        $service->delete();

        $this->get(route('landing'))->assertDontSee('اسم جديد', false);
    }

    public function test_cached_services_survive_a_store_that_refuses_to_unserialize_objects(): void
    {
        config(['cache.stores.array.serialize' => true, 'cache.serializable_classes' => false]);
        Cache::forgetDriver('array');
        Service::factory()->create(['title' => 'خدمات النظافة', 'slug' => 'cleaning']);

        app(ServiceService::class)->listActive();
        $cached = app(ServiceService::class)->listActive();

        $this->assertInstanceOf(Service::class, $cached->first());
        $this->assertSame('خدمات النظافة', $cached->first()->displayTitle());
        $this->assertSame(route('services.show', 'cleaning'), route('services.show', $cached->first()));
    }

    public function test_layout_defers_animation_scripts_before_the_app_module(): void
    {
        $html = $this->get(route('landing'))->getContent();

        $this->assertStringContainsString('gsap.min.js" defer></script>', $html);
        $this->assertStringContainsString('ScrollTrigger.min.js" defer></script>', $html);
        $this->assertStringContainsString('family=Cairo', $html);
        $this->assertStringContainsString('&display=swap', $html);
        $this->assertStringContainsString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>', $html);
        $this->assertLessThan(strpos($html, 'type="module"'), strpos($html, 'ScrollTrigger.min.js'));
    }

    private function seedCatalog(int $services, int $projectsPerService = 1): void
    {
        Project::query()->delete();
        Service::query()->withInactive()->get()->each->delete();

        for ($i = 1; $i <= $services; $i++) {
            $service = Service::factory()->create(['slug' => 'service-'.$i]);
            Project::factory()->count($projectsPerService)->forService($service)->create();
        }
    }

    private function queriesFor(string $url): int
    {
        Cache::flush();
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get($url)->assertOk();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }
}
