<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PrivacyPolicySection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PrivacyPolicyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_a_privacy_section(): void
    {
        $this->postJson('/api/admin/privacy-policy', [
            'title' => 'بند تجريبي',
            'description' => 'وصف البند',
        ])->assertUnauthorized();
    }

    public function test_empty_payload_returns_422_for_title_and_description(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/admin/privacy-policy', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.title.0', 'عنوان البند مطلوب.')
            ->assertJsonPath('errors.description.0', 'وصف البند مطلوب.');
    }

    public function test_valid_payload_creates_a_section_and_returns_201(): void
    {
        $user = User::factory()->create();

        $created = $this->actingAs($user)
            ->postJson('/api/admin/privacy-policy', [
                'title' => 'البيانات التي نجمعها',
                'title_en' => 'Data we collect',
                'description' => 'نجمع الاسم ورقم الجوال.',
                'description_en' => 'We collect your name and mobile number.',
                'order_column' => 2,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('title', 'البيانات التي نجمعها')
            ->assertJsonPath('order_column', 2);

        $this->assertDatabaseHas('privacy_policy_sections', [
            'id' => $created->json('id'),
            'title' => 'البيانات التي نجمعها',
            'description' => 'نجمع الاسم ورقم الجوال.',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_a_section_and_hide_it(): void
    {
        $user = User::factory()->create();
        $section = PrivacyPolicySection::factory()->create([
            'title' => 'البند القديم',
            'description' => 'الوصف القديم',
        ]);

        $this->actingAs($user)
            ->putJson('/api/admin/privacy-policy/'.$section->id, [
                'title' => 'حقوقك',
                'description' => 'يمكنك طلب حذف بياناتك.',
                'order_column' => 1,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('title', 'حقوقك')
            ->assertJsonPath('is_active', false);

        $this->assertDatabaseHas('privacy_policy_sections', [
            'id' => $section->id,
            'title' => 'حقوقك',
            'description' => 'يمكنك طلب حذف بياناتك.',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_a_section(): void
    {
        $user = User::factory()->create();
        $section = PrivacyPolicySection::factory()->create();

        $this->actingAs($user)
            ->deleteJson('/api/admin/privacy-policy/'.$section->id)
            ->assertOk();

        $this->assertDatabaseMissing('privacy_policy_sections', [
            'id' => $section->id,
        ]);
    }

    public function test_admin_list_includes_hidden_sections(): void
    {
        $user = User::factory()->create();
        PrivacyPolicySection::factory()->inactive()->create([
            'title' => 'بند مخفي',
        ]);

        $this->actingAs($user)
            ->getJson('/api/admin/privacy-policy?all=1')
            ->assertOk()
            ->assertJsonFragment(['title' => 'بند مخفي']);
    }

    public function test_public_page_renders_active_sections_in_order_and_escapes_html(): void
    {
        PrivacyPolicySection::factory()->create([
            'title' => 'البند الثاني',
            'description' => 'يظهر بعد الأول.',
            'order_column' => 2,
        ]);
        PrivacyPolicySection::factory()->create([
            'title' => '<script>alert(1)</script>',
            'description' => 'وصف <b>الأول</b>',
            'order_column' => 1,
        ]);
        PrivacyPolicySection::factory()->inactive()->create([
            'title' => 'بند مخفي عن الزوار',
            'description' => 'لا يظهر في الصفحة.',
            'order_column' => 0,
        ]);

        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('<h1>سياسة الخصوصية</h1>', false)
            ->assertDontSee('نوضح في هذه الصفحة كيف تتعامل ركيزة', false)
            ->assertSee('في هذه الصفحة', false)
            ->assertDontSee('للاستفسار عن بياناتك', false)
            ->assertDontSee('class="policy-help"', false)
            ->assertSee('name="description"', false)
            ->assertSee('"@type":"WebPage"', false)
            ->assertSeeInOrder([
                '&lt;script&gt;alert(1)&lt;/script&gt;',
                'البند الثاني',
            ], false)
            ->assertSee('وصف &lt;b&gt;الأول&lt;/b&gt;', false)
            ->assertDontSee('بند مخفي عن الزوار', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_english_page_uses_english_copy_and_falls_back_to_arabic(): void
    {
        PrivacyPolicySection::factory()->create([
            'title' => 'حقوقك',
            'title_en' => 'Your rights',
            'description' => 'يمكنك طلب الحذف.',
            'description_en' => 'You can ask for deletion.',
            'order_column' => 1,
        ]);
        PrivacyPolicySection::factory()->create([
            'title' => 'بند عربي فقط',
            'title_en' => null,
            'description' => 'وصف عربي فقط.',
            'description_en' => null,
            'order_column' => 2,
        ]);

        $this->withSession(['locale' => 'en'])
            ->get(route('privacy'))
            ->assertOk()
            ->assertSee('<h1>Privacy policy</h1>', false)
            ->assertSee('On this page', false)
            ->assertDontSee('Questions about your data', false)
            ->assertDontSee('في هذه الصفحة', false)
            ->assertSee('Your rights', false)
            ->assertSee('You can ask for deletion.', false)
            ->assertSee('بند عربي فقط', false)
            ->assertDontSee('حقوقك', false);
    }

    public function test_empty_policy_page_shows_the_empty_message(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('سيتم نشر بنود سياسة الخصوصية قريباً.', false)
            ->assertDontSee('للاستفسار عن بياناتك', false)
            ->assertDontSee('class="policy-toc"', false);
    }

    public function test_homepage_footer_links_to_the_privacy_page(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee(route('privacy'), false)
            ->assertSee('سياسة الخصوصية', false);
    }

    public function test_sitemap_lists_the_privacy_page(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('privacy'), false);
    }
}
