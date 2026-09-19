<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicLayoutChromeTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_uses_shared_responsive_chrome(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('menu-toggle', false)
            ->assertSee('id="main-nav"', false)
            ->assertSee('nav-overlay', false)
            ->assertSee('footer-grid', false)
            ->assertSee('floating-whatsapp', false)
            ->assertSee('انضم إلينا كحرفي', false)
            ->assertSee('lang-switch', false)
            ->assertSee('lang="en"', false)
            ->assertSee('>الإنجليزية</span>', false);
    }

    public function test_service_page_uses_the_same_header_and_footer(): void
    {
        $service = Service::factory()->create([
            'title' => 'خدمات النظافة',
        ]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('menu-toggle', false)
            ->assertSee('footer-grid', false)
            ->assertSee('floating-whatsapp', false)
            ->assertSee('خدماتنا', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_craftsman_page_uses_the_same_header_and_footer(): void
    {
        $this->get(route('craftsman.create'))
            ->assertOk()
            ->assertSee('menu-toggle', false)
            ->assertSee('footer-grid', false)
            ->assertSee('floating-whatsapp', false)
            ->assertSee('تسجيل الحرفيين', false)
            ->assertSee('تواصل معنا', false);
    }
}
