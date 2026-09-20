<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Craftsman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CraftsmanSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_collects_name_parts_and_national_id(): void
    {
        $this->withSession(['locale' => 'ar'])
            ->get(route('craftsman.create'))
            ->assertOk()
            ->assertSee('الاسم الأول')
            ->assertSee('اسم الأب')
            ->assertSee('اسم الجد')
            ->assertSee('اسم العائلة')
            ->assertSee('رقم الهوية')
            ->assertSee('يمكنك اختيار أكثر من تخصص.')
            ->assertSee('class="form-field-heading"', false)
            ->assertSee('name="specialties[]"', false)
            ->assertSee('name="has_tools" type="radio" value="1"', false)
            ->assertSee('name="has_tools" type="radio" value="0"', false)
            ->assertSee('name="website"', false)
            ->assertSee('fa-shield-halved', false)
            ->assertSee('fa-users', false)
            ->assertSee('fa-briefcase', false)
            ->assertSee('fa-phone', false)
            ->assertSee('إرسال طلب الانضمام');
    }

    public function test_craftsman_can_register_with_name_parts_and_national_id(): void
    {
        $this->postJson(route('craftsman.store'), $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('message', 'تم استلام طلب انضمامك، وسيتواصل معك فريق ركيزة قريباً.');

        $craftsman = Craftsman::query()->sole();

        $this->assertSame('محمد أحمد محمود النجار', $craftsman->name);
        $this->assertSame('401234567', $craftsman->national_id);
        $this->assertSame(['سباكة', 'كهرباء'], $craftsman->specialty);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/admin/craftsmen')
            ->assertOk()
            ->assertJsonPath('data.0.id', $craftsman->id)
            ->assertJsonPath('data.0.national_id', '401234567');
    }

    public function test_duplicate_national_id_is_rejected_with_clear_message(): void
    {
        $this->postJson(route('craftsman.store'), $this->validPayload())
            ->assertCreated();

        $this->postJson(route('craftsman.store'), [
            ...$this->validPayload(),
            'phone' => '0599999999',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.national_id.0', 'رقم الهوية مسجل بالفعل.');

        $this->assertDatabaseCount('craftsmen', 1);
    }

    public function test_national_id_must_contain_exactly_nine_digits(): void
    {
        $this->postJson(route('craftsman.store'), [
            ...$this->validPayload(),
            'national_id' => '12345',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.national_id.0', 'يجب أن يتكوّن رقم الهوية من 9 أرقام.');

        $this->assertDatabaseCount('craftsmen', 0);
    }

    public function test_craftsman_can_choose_that_they_do_not_have_tools(): void
    {
        $this->postJson(route('craftsman.store'), [
            ...$this->validPayload(),
            'has_tools' => false,
        ])->assertCreated();

        $this->assertFalse(Craftsman::query()->sole()->has_tools);
    }

    public function test_tools_choice_is_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['has_tools']);

        $this->postJson(route('craftsman.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonPath('errors.has_tools.0', 'اختر نعم أو لا لخيار المعدات والأدوات.');

        $this->assertDatabaseCount('craftsmen', 0);
    }

    public function test_honeypot_field_blocks_bots_without_saving(): void
    {
        $this->postJson(route('craftsman.store'), [
            ...$this->validPayload(),
            'website' => 'http://spam.test',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'تم استلام طلب انضمامك، وسيتواصل معك فريق ركيزة قريباً.');

        $this->assertDatabaseCount('craftsmen', 0);
    }

    public function test_craftsman_requests_are_rate_limited_per_ip(): void
    {
        foreach (range(1, 3) as $index) {
            $this->postJson(route('craftsman.store'), [
                ...$this->validPayload(),
                'national_id' => (string) (401234560 + $index),
                'phone' => '059123456'.$index,
            ])->assertCreated();
        }

        $this->postJson(route('craftsman.store'), [
            ...$this->validPayload(),
            'national_id' => '401234569',
            'phone' => '0591234560',
        ])
            ->assertTooManyRequests()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'تم إرسال عدد كبير من الطلبات. حاول مرة أخرى بعد دقيقة.');
    }

    public function test_craftsman_validation_rejects_invalid_payloads(): void
    {
        $this->postJson(route('craftsman.store'), [
            'first_name' => 'محمد123',
            'father_name' => 'أ',
            'grandfather_name' => 'محمود',
            'family_name' => 'النجار',
            'national_id' => '401234567',
            'phone' => '0501234567',
            'city' => 'غزة',
            'specialties' => ['سباكة'],
            'experience_years' => 7,
            'has_tools' => true,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['first_name', 'father_name', 'phone']);

        $this->assertDatabaseCount('craftsmen', 0);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'first_name' => 'محمد',
            'father_name' => 'أحمد',
            'grandfather_name' => 'محمود',
            'family_name' => 'النجار',
            'national_id' => '401234567',
            'phone' => '0591234567',
            'city' => 'غزة',
            'specialties' => ['سباكة', 'كهرباء'],
            'experience_years' => 7,
            'has_tools' => true,
            'bio' => 'خبرة في صيانة المنازل.',
        ];
    }
}
