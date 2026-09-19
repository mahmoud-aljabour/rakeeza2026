<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CraftsmanStatus;
use App\Models\Craftsman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use ZipArchive;

final class CraftsmanExcelExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_export_craftsmen(): void
    {
        $this->get('/api/admin/craftsmen/export')->assertUnauthorized();
    }

    public function test_admin_can_export_craftsmen_as_excel(): void
    {
        $user = User::factory()->create();
        Craftsman::query()->create([
            'name' => 'أحمد النجار',
            'phone' => '0597000000',
            'city' => 'غزة',
            'specialty' => ['نجارة'],
            'experience_years' => 5,
            'has_tools' => true,
            'bio' => 'حرفي متخصص',
            'status' => CraftsmanStatus::Pending,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['locale' => 'ar'])
            ->get('/api/admin/craftsmen/export')
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            );

        $path = $this->storeExportedXlsx($this->exportedBinary($response));
        $this->assertSheetDirection($path, rightToLeft: true);
        $this->assertXlsxContains($path, 'طلبات تسجيل الحرفيين');
        $this->assertXlsxContains($path, 'الاسم');
        unlink($path);
    }

    public function test_english_export_uses_ltr_layout_and_english_labels(): void
    {
        $user = User::factory()->create();
        Craftsman::query()->create([
            'name' => 'Omar',
            'phone' => '0597111111',
            'city' => 'Ramallah',
            'specialty' => ['Plumbing'],
            'experience_years' => 3,
            'has_tools' => false,
            'bio' => 'Reliable craftsman',
            'status' => CraftsmanStatus::Pending,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get('/api/admin/craftsmen/export')
            ->assertOk();

        $path = $this->storeExportedXlsx($this->exportedBinary($response));
        $this->assertSheetDirection($path, rightToLeft: false);
        $this->assertXlsxContains($path, 'Craftsman registration requests');
        $this->assertXlsxContains($path, 'Specialty');
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

        $path = tempnam(sys_get_temp_dir(), 'craftsman-xlsx-');
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
