<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\QuoteRequestMail;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class LeadEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_storing_a_lead_sends_a_quote_request_email(): void
    {
        Mail::fake();

        $service = Service::factory()->create([
            'title' => 'خدمات النظافة',
        ]);

        $this->postJson(route('leads.store'), [
            'name' => 'أحمد علي',
            'phone' => '0591234567',
            'email' => 'ahmed@example.com',
            'service_id' => $service->id,
            'message' => 'أريد تنظيف منشأة.',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('mail_sent', true)
            ->assertJsonPath('message', 'تم إرسال طلبك بنجاح، سيتواصل معك فريق ركيزة قريباً');

        $this->assertDatabaseHas('leads', [
            'name' => 'أحمد علي',
            'phone' => '0591234567',
            'email' => 'ahmed@example.com',
            'service_id' => $service->id,
        ]);

        Mail::assertSent(QuoteRequestMail::class, function (QuoteRequestMail $mail): bool {
            return $mail->hasTo('info@rakeeza-ps.com')
                && $mail->hasFrom((string) config('mail.from.address'))
                && $mail->hasReplyTo('ahmed@example.com')
                && $mail->customerName === 'أحمد علي'
                && $mail->phone === '0591234567'
                && $mail->customerEmail === 'ahmed@example.com'
                && $mail->serviceType === 'خدمات النظافة'
                && $mail->details === 'أريد تنظيف منشأة.'
                && $mail->envelope()->subject === 'طلب عرض سعر جديد من أحمد علي - ركيزة';
        });
    }

    public function test_general_inquiry_is_accepted_and_emailed(): void
    {
        Mail::fake();

        $this->postJson(route('leads.store'), [
            'name' => 'سارة محمد',
            'phone' => '+970561234567',
            'email' => 'sara@example.com',
            'service_id' => 'general',
            'message' => 'استفسار عن خدمات المؤسسات.',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('leads', [
            'name' => 'سارة محمد',
            'service_id' => null,
        ]);

        Mail::assertSent(QuoteRequestMail::class, function (QuoteRequestMail $mail): bool {
            return $mail->serviceType === 'استفسار عام';
        });
    }

    public function test_lead_validation_rejects_invalid_payloads(): void
    {
        Mail::fake();

        $this->postJson(route('leads.store'), [
            'name' => 'أحمد123',
            'phone' => '0501234567',
            'email' => 'not-an-email',
            'service_id' => 999999,
            'message' => str_repeat('س', 1001),
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['name', 'phone', 'email', 'service_id', 'message']);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_honeypot_field_blocks_bots_without_sending_mail(): void
    {
        Mail::fake();

        $this->postJson(route('leads.store'), [
            'name' => 'بوت تجريبي',
            'phone' => '0591234567',
            'email' => 'bot@example.com',
            'service_id' => 'general',
            'message' => 'رسالة مزعجة',
            'website' => 'http://spam.test',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_quote_requests_are_rate_limited_per_ip(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'أحمد علي',
            'phone' => '0591234567',
            'email' => 'ahmed@example.com',
            'service_id' => 'general',
            'message' => 'طلب تجريبي',
        ];

        $this->postJson(route('leads.store'), $payload)->assertOk();
        $this->postJson(route('leads.store'), $payload)->assertOk();
        $this->postJson(route('leads.store'), $payload)->assertOk();

        $this->postJson(route('leads.store'), $payload)
            ->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'تم إرسال عدد كبير من الطلبات. حاول مرة أخرى بعد دقيقة.');

        Mail::assertSent(QuoteRequestMail::class, 3);
    }

    public function test_landing_form_sends_email_instead_of_whatsapp(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('أرسل طلبك', false)
            ->assertSee('إرسال الطلب', false)
            ->assertSee('fa-envelope', false)
            ->assertSee('name="website"', false)
            ->assertSee('id="contact-email"', false)
            ->assertSee('البريد الإلكتروني', false)
            ->assertDontSee('إرسال عبر واتساب', false)
            ->assertDontSee('data-whatsapp', false)
            ->assertSee('floating-whatsapp', false)
            ->assertSee('is-whatsapp', false);
    }
}
