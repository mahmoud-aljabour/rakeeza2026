<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Support\AppLocale;
use App\Support\ExcelExportStyle;
use Illuminate\Database\Eloquent\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class LeadExcelExportService
{
    /**
     * @param  Collection<int, Lead>  $leads
     */
    public function downloadList(
        Collection $leads,
        ?LeadStatus $status = null,
        ?string $from = null,
        ?string $to = null,
    ): BinaryFileResponse {
        $locale = AppLocale::current();
        $filename = ($locale === AppLocale::ENGLISH
            ? 'customer-leads-'
            : 'طلبات-العملاء-').now()->format('Y-m-d').'.xlsx';

        $path = $this->writeWorkbook(function (Writer $writer) use ($leads, $status, $from, $to, $locale): void {
            $headings = $this->headings($locale);
            $columnCount = count($headings);
            $headerRow = 4;
            $lastDataRow = $headerRow + max(0, $leads->count());

            ExcelExportStyle::configureSheet(
                $writer->getCurrentSheet(),
                $locale === AppLocale::ENGLISH ? 'Customer leads' : 'طلبات العملاء',
                $columnCount,
                $headerRow,
                max($headerRow, $lastDataRow),
                [6, 22, 16, 28, 24, 36, 14, 28, 12, 18],
            );

            $writer->addRow(Row::fromValuesWithStyle([
                $this->copy('title', $locale),
            ], ExcelExportStyle::titleStyle(), 24));

            $writer->addRow(Row::fromValuesWithStyle([
                $this->copy('status', $locale).': '.($status?->label($locale) ?? $this->copy('all', $locale)),
                $this->periodLabel($locale, $from, $to),
                $this->copy('exported_at', $locale).': '.now()->format('Y-m-d H:i'),
                $this->copy('count', $locale).': '.$leads->count(),
            ], ExcelExportStyle::metaStyle(), 20));

            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValuesWithStyle($headings, ExcelExportStyle::headerStyle(), 22));

            foreach ($leads->values() as $index => $lead) {
                $writer->addRow(Row::fromValuesWithStyle(
                    $this->rowValues($lead, $index + 1, $locale),
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
                'Email',
                'Service',
                'Details',
                'Status',
                'Latest note',
                'Notes count',
                'Requested at',
            ];
        }

        return [
            '#',
            'الاسم',
            'الجوال',
            'البريد الإلكتروني',
            'الخدمة',
            'التفاصيل',
            'الحالة',
            'آخر ملاحظة',
            'عدد الملاحظات',
            'تاريخ الطلب',
        ];
    }

    /**
     * @return list<int|string|null>
     */
    private function rowValues(Lead $lead, int $index, string $locale): array
    {
        $notes = $lead->notes;
        $latestNote = $notes->first();
        $separator = $locale === AppLocale::ENGLISH ? ', ' : '، ';
        $emptyNote = $locale === AppLocale::ENGLISH ? 'No note text' : 'بدون نص ملاحظة';
        $generalInquiry = $locale === AppLocale::ENGLISH ? 'General inquiry' : 'استفسار عام';

        $serviceTitles = [];
        if ($lead->relationLoaded('services') && $lead->services->isNotEmpty()) {
            $serviceTitles = $lead->services
                ->map(static fn ($service): string => $service->displayTitle())
                ->all();
        } elseif ($lead->service) {
            $serviceTitles = [$lead->service->displayTitle()];
        }

        return [
            $index,
            $lead->name,
            $lead->phone,
            $lead->email,
            $serviceTitles !== [] ? implode($separator, $serviceTitles) : $generalInquiry,
            $lead->message,
            $lead->status->label($locale),
            $latestNote?->note ?: ($latestNote ? $emptyNote : null),
            $notes->count(),
            $lead->created_at?->format('Y-m-d H:i'),
        ];
    }

    private function copy(string $key, string $locale): string
    {
        $english = [
            'title' => 'Customer leads Rakeeza',
            'status' => 'Status',
            'all' => 'All',
            'period' => 'Period',
            'period_all' => 'All dates',
            'period_from' => 'From',
            'period_to' => 'To',
            'exported_at' => 'Exported at',
            'count' => 'Count',
        ];

        $arabic = [
            'title' => 'طلبات العملاء ركيزة',
            'status' => 'الحالة',
            'all' => 'الكل',
            'period' => 'الفترة',
            'period_all' => 'كل التواريخ',
            'period_from' => 'من',
            'period_to' => 'إلى',
            'exported_at' => 'تاريخ التصدير',
            'count' => 'العدد',
        ];

        return ($locale === AppLocale::ENGLISH ? $english : $arabic)[$key];
    }

    private function periodLabel(string $locale, ?string $from, ?string $to): string
    {
        if (! filled($from) && ! filled($to)) {
            return $this->copy('period', $locale).': '.$this->copy('period_all', $locale);
        }

        if (filled($from) && filled($to)) {
            return $this->copy('period', $locale).': '.$from.' → '.$to;
        }

        if (filled($from)) {
            return $this->copy('period', $locale).': '.$this->copy('period_from', $locale).' '.$from;
        }

        return $this->copy('period', $locale).': '.$this->copy('period_to', $locale).' '.$to;
    }

    /**
     * @param  callable(Writer): void  $writerCallback
     */
    private function writeWorkbook(callable $writerCallback): string
    {
        $path = tempnam(sys_get_temp_dir(), 'rakeeza-leads-');
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
