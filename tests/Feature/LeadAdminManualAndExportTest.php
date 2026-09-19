<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use ZipArchive;

final class LeadAdminManualAndExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_lead_manually(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'title' => 'خدمات النظافة',
        ]);

        $this->actingAs($user)
            ->postJson('/api/admin/leads', [
                'name' => 'محمود جبور',
                'phone' => '0598855299',
                'email' => 'mahmoud@example.com',
                'service_id' => $service->id,
                'message' => 'طلب عبر الهاتف',
                'note' => 'تم التسجيل يدوياً من المكتب.',
            ])
            ->assertCreated()
            ->assertJsonPath('name', 'محمود جبور')
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('service.title', 'خدمات النظافة')
            ->assertJsonPath('notes.0.note', 'تم التسجيل يدوياً من المكتب.');

        $this->assertDatabaseHas('leads', [
            'name' => 'محمود جبور',
            'phone' => '0598855299',
            'status' => LeadStatus::Pending->value,
        ]);
    }

    public function test_admin_can_export_leads_as_excel(): void
    {
        $user = User::factory()->create();
        Lead::query()->create([
            'name' => 'عبدالله',
            'phone' => '0597000000',
            'email' => 'lead@example.com',
            'message' => 'استفسار',
            'status' => LeadStatus::Pending,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['locale' => 'ar'])
            ->get('/api/admin/leads/export')
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            );

        $path = $this->storeExportedXlsx($this->exportedBinary($response));
        $this->assertSheetDirection($path, rightToLeft: true);
        $this->assertXlsxContains($path, 'طلبات العملاء');
        $this->assertXlsxContains($path, 'الاسم');
        unlink($path);
    }

    public function test_english_lead_export_uses_ltr_layout_and_english_labels(): void
    {
        $user = User::factory()->create();
        Lead::query()->create([
            'name' => 'Abdullah',
            'phone' => '0597000000',
            'email' => 'lead@example.com',
            'message' => 'Inquiry',
            'status' => LeadStatus::Pending,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get('/api/admin/leads/export')
            ->assertOk();

        $path = $this->storeExportedXlsx($this->exportedBinary($response));
        $this->assertSheetDirection($path, rightToLeft: false);
        $this->assertXlsxContains($path, 'Customer leads');
        $this->assertXlsxContains($path, 'Latest note');
        $this->assertXlsxContains($path, 'New');
        unlink($path);
    }

    private function exportedBinary(TestResponse $response): string
    {
        $file = $response->baseResponse->getFile();

        $this->assertNotNull($file);

        return (string) file_get_contents($file->getPathname());
    }

    private function storeExportedXlsx(string|false $binary): string
    {
        $this->assertIsString($binary);
        $this->assertNotSame('', $binary);

        $path = tempnam(sys_get_temp_dir(), 'lead-xlsx-');
        $this->assertIsString($path);

        $xlsxPath = $path.'.xlsx';
        rename($path, $xlsxPath);
        file_put_contents($xlsxPath, $binary);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($xlsxPath) === true);
        $zip->close();

        return $xlsxPath;
    }

    private function assertSheetDirection(string $xlsxPath, bool $rightToLeft): void
    {
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($xlsxPath) === true);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        $this->assertIsString($sheetXml);
        $this->assertStringContainsString(
            'rightToLeft="'.($rightToLeft ? 'true' : 'false').'"',
            $sheetXml,
        );
    }

    private function assertXlsxContains(string $xlsxPath, string $needle): void
    {
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($xlsxPath) === true);
        $sheetXml = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $shared = (string) ($zip->getFromName('xl/sharedStrings.xml') ?: '');
        $zip->close();

        $this->assertTrue(
            str_contains($sheetXml, $needle) || str_contains($shared, $needle),
            "Failed asserting that the workbook contains \"{$needle}\".",
        );
    }
}
