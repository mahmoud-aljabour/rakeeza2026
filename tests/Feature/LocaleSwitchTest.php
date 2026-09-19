<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_default_to_arabic(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('الرئيسية', false)
            ->assertSee('>الإنجليزية</span>', false);
    }

    public function test_guest_can_switch_the_site_to_english(): void
    {
        $this->from(route('landing'))
            ->get(route('locale.switch', 'en'))
            ->assertRedirect(route('landing'));

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Home', false)
            ->assertSee('Our complete services', false)
            ->assertSee('Contact us to request a service', false)
            ->assertSee('Quick links', false)
            ->assertSee('Join as craftsman', false)
            ->assertSee('Practical restoration and facility solutions', false)
            ->assertSee('All rights reserved.', false)
            ->assertSee('>العربية</span>', false)
            ->assertDontSee('خدماتنا الشاملة', false);
    }

    public function test_english_locale_translates_known_catalog_content(): void
    {
        $service = Service::factory()->create([
            'title' => 'خدمات النظافة',
            'title_en' => 'Cleaning services',
            'description' => 'وصف خدمة النظافة الكامل.',
            'description_en' => 'Full cleaning service description.',
        ]);

        $this->withSession(['locale' => 'en'])
            ->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('Cleaning services', false)
            ->assertSee('Full cleaning service description.', false)
            ->assertSee('Service details', false)
            ->assertSee('Request price via WhatsApp', false)
            ->assertSee('Send request', false)
            ->assertDontSee('تفاصيل الخدمة', false);
    }

    public function test_unknown_locale_is_rejected(): void
    {
        $this->get('/locale/fr')->assertNotFound();
    }

    public function test_admin_shell_follows_the_current_locale(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('RakeezaAdmin', false)
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('لوحة التحكم', false);

        $this->withSession(['locale' => 'en'])
            ->get('/admin')
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Admin', false);
    }
}
