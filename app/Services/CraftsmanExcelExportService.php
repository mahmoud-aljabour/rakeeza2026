<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CraftsmanStatus;
use App\Models\Craftsman;
use App\Support\AppLocale;
use App\Support\ExcelExportStyle;
use Illuminate\Database\Eloquent\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class CraftsmanExcelExportService
{
    /**
     * @param  Collection<int, Craftsman>  $craftsmen
     */
    public function downloadList(Collection $craftsmen, ?CraftsmanStatus $status = null): BinaryFileResponse
    {
        $locale = AppLocale::current();
        $filename = ($locale === AppLocale::ENGLISH
            ? 'craftsman-requests-'
            : 'طلبات-الحرفيين-').now()->format('Y-m-d').'.xlsx';

        $path = $this->writeWorkbook(function (Writer $writer) use ($craftsmen, $status, $locale): void {
            $headings = $this->headings($locale);
            $columnCount = count($headings);
            $headerRow = 4;
            $lastDataRow = $headerRow + max(0, $craftsmen->count());

            ExcelExportStyle::configureSheet(
                $writer->getCurrentSheet(),
                $locale === AppLocale::ENGLISH ? 'Craftsman requests' : 'طلبات الحرفيين',
                $columnCount,
                $headerRow,
                max($headerRow, $lastDataRow),
                [6, 22, 16, 16, 28, 12, 10, 36, 14, 18],
            );

            $writer->addRow(Row::fromValuesWithStyle([
                $this->copy('title', $locale),
            ], ExcelExportStyle::titleStyle(), 24));

            $writer->addRow(Row::fromValuesWithStyle([
                $this->copy('status', $locale).': '.($status?->label($locale) ?? $this->copy('all', $locale)),
                $this->copy('exported_at', $locale).': '.now()->format('Y-m-d H:i'),
                $this->copy('count', $locale).': '.$craftsmen->count(),
            ], ExcelExportStyle::metaStyle(), 20));

            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValuesWithStyle($headings, ExcelExportStyle::headerStyle(), 22));

            foreach ($craftsmen->values() as $index => $craftsman) {
                $writer->addRow(Row::fromValuesWithStyle(
                    $this->rowValues($craftsman, $index + 1, $locale),
                    ExcelExportStyle::dataStyle($index % 2 === 1),
                    18,
                ));
            }
        });

        return $this->downloadResponse($path, $filename);
    }

    /**
     * @return list<string>
     */
    private function headings(string $locale): array
    {
        if ($locale === AppLocale::ENGLISH) {
            return [
                '#',
                'Name',
                'Phone',
                'Area',
                'Specialty',
                'Experience',
                'Tools',
                'About',
                'Status',
                'Requested at',
            ];
        }

        return [
            '#',
            'الاسم',
            'الجوال',
            'المنطقة',
            'التخصص',
            'الخبرة',
            'معدات',
            'نبذة',
            'الحالة',
            'تاريخ الطلب',
        ];
    }

    /**
     * @return list<int|string|null>
     */
    private function rowValues(Craftsman $craftsman, int $index, string $locale): array
    {
        $separator = $locale === AppLocale::ENGLISH ? ', ' : '، ';
        $years = $locale === AppLocale::ENGLISH
            ? $craftsman->experience_years.' years'
            : $craftsman->experience_years.' سنة';
        $tools = $locale === AppLocale::ENGLISH
            ? ($craftsman->has_tools ? 'Yes' : 'No')
            : ($craftsman->has_tools ? 'نعم' : 'لا');

        return [
            $index,
            $craftsman->name,
            $craftsman->phone,
            $craftsman->city,
            $craftsman->specialtiesLabel($separator),
            $years,
            $tools,
            $craftsman->bio,
            $craftsman->status->label($locale),
            $craftsman->created_at?->format('Y-m-d H:i'),
        ];
    }

    private function copy(string $key, string $locale): string
    {
        $english = [
            'title' => 'Craftsman registration requests — Rakeeza',
            'status' => 'Status',
            'all' => 'All',
            'exported_at' => 'Exported at',
            'count' => 'Count',
        ];

        $arabic = [
            'title' => 'طلبات تسجيل الحرفيين — ركيزة',
            'status' => 'الحالة',
            'all' => 'الكل',
            'exported_at' => 'تاريخ التصدير',
            'count' => 'العدد',
        ];

        return ($locale === AppLocale::ENGLISH ? $english : $arabic)[$key];
    }

    /**
     * @param  callable(Writer): void  $writerCallback
     */
    private function writeWorkbook(callable $writerCallback): string
    {
        $path = tempnam(sys_get_temp_dir(), 'rakeeza-craftsmen-');
        if ($path === false) {
            throw new \RuntimeException('Unable to create a temporary export file.');
        }

        $xlsxPath = $path.'.xlsx';
        if (! rename($path, $xlsxPath)) {
            @unlink($path);
            throw new \RuntimeException('Unable to prepare the export file path.');
        }

        $writer = new Writer;
        $writer->openToFile($xlsxPath);

        try {
            $writerCallback($writer);
        } finally {
            $writer->close();
        }

        return $xlsxPath;
    }

    private function downloadResponse(string $path, string $filename): BinaryFileResponse
    {
        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache',
        ])->deleteFileAfterSend(true);
    }
}
