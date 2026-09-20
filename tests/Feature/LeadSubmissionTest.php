<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class LeadSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_is_saved_with_multiple_services_without_sending_email(): void
    {
        Mail::fake();

        $firstService = Service::factory()->create(['title' => 'خدمات النظافة']);
        $secondService = Service::factory()->create(['title' => 'خدمات الصيانة']);

        $this->postJson(route('leads.store'), [
            'name' => 'أحمد علي',
            'phone' => '0591234567',
            'email' => 'ahmed@example.com',
            'service_ids' => [$firstService->id, $secondService->id],
            'message' => 'أحتاج إلى الخدمتين.',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'شكراً لتواصلكم. تم استلام طلبكم بنجاح، وسيتواصل معكم فريق ركيزة قريباً.');

        $lead = Lead::query()->where('phone', '0591234567')->firstOrFail();

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'service_id' => $firstService->id,
        ]);
        $this->assertDatabaseHas('lead_service', [
            'lead_id' => $lead->id,
            'service_id' => $firstService->id,
        ]);
        $this->assertDatabaseHas('lead_service', [
            'lead_id' => $lead->id,
            'service_id' => $secondService->id,
        ]);
        Mail::assertNothingSent();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/admin/leads')
            ->assertOk()
            ->assertJsonFragment(['title' => 'خدمات النظافة'])
            ->assertJsonFragment(['title' => 'خدمات الصيانة']);
    }

    public function test_general_inquiry_is_saved_without_a_service_or_email(): void
    {
        Mail::fake();

        $this->postJson(route('leads.store'), [
            'name' => 'سارة محمد',
            'phone' => '+970561234567',
            'email' => 'sara@example.com',
            'service_ids' => ['general'],
            'message' => 'استفسار عام عن خدمات المؤسسات.',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('leads', [
            'name' => 'سارة محمد',
            'service_id' => null,
        ]);
        $this->assertDatabaseCount('lead_service', 0);
        Mail::assertNothingSent();
    }

    public function test_lead_validation_rejects_invalid_payloads(): void
    {
        Mail::fake();

        $this->postJson(route('leads.store'), [
            'name' => 'أحمد123',
            'phone' => '0501234567',
            'email' => 'not-an-email',
            'service_ids' => [999999],
            'message' => str_repeat('س', 1001),
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['name', 'phone', 'email', 'service_ids', 'message']);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_honeypot_field_blocks_bots_without_saving_or_sending_email(): void
    {
        Mail::fake();

        $this->postJson(route('leads.store'), [
            'name' => 'بوت تجريبي',
            'phone' => '0591234567',
            'email' => 'bot@example.com',
            'service_ids' => ['general'],
            'message' => 'رسالة مزعجة',
            'website' => 'http://spam.test',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_quote_requests_are_rate_limited_per_ip_without_sending_email(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'أحمد علي',
            'phone' => '0591234567',
            'email' => 'ahmed@example.com',
            'service_ids' => ['general'],
            'message' => 'طلب تجريبي',
        ];

        $this->postJson(route('leads.store'), $payload)->assertOk();
        $this->postJson(route('leads.store'), $payload)->assertOk();
        $this->postJson(route('leads.store'), $payload)->assertOk();

        $this->postJson(route('leads.store'), $payload)
            ->assertTooManyRequests()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'تم إرسال عدد كبير من الطلبات. حاول مرة أخرى بعد دقيقة.');

        Mail::assertNothingSent();
    }

    public function test_public_forms_allow_selecting_multiple_services(): void
    {
        $service = Service::factory()->create(['title' => 'خدمات النظافة']);
        Service::factory()->create(['title' => 'خدمات الصيانة']);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('سيظهر طلبك مباشرة لفريق ركيزة في لوحة التحكم', false)
            ->assertSee('name="service_ids[]"', false)
            ->assertSee('يمكنك اختيار أكثر من خدمة.', false)
            ->assertSee('<details class="service-multiselect" id="contact-service">', false)
            ->assertDontSee('<select id="contact-service"', false);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('name="service_ids[]"', false)
            ->assertSee('<details class="service-multiselect" id="contact-service">', false)
            ->assertSee('يمكنك اختيار أكثر من خدمة.', false);
    }
}
