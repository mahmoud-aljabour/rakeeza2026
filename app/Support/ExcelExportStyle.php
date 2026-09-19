<?php

declare(strict_types=1);

namespace App\Support;

use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderName;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\BorderStyle;
use OpenSpout\Common\Entity\Style\BorderWidth;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Entity\SheetView;

final class ExcelExportStyle
{
    private const string BRAND = '0A3356';

    private const string HEADER_BG = '0A3356';

    private const string META_BG = 'E8EEF4';

    private const string ALT_ROW_BG = 'F3F6F9';

    private const string BORDER = 'CBD5E1';

    public static function configureSheet(
        Sheet $sheet,
        string $name,
        int $columnCount,
        int $headerRow,
        int $lastDataRow,
        array $columnWidths,
    ): void {
        $sheet->setName(mb_substr($name, 0, 31));
        $sheet->setSheetView(
            (new SheetView)
                ->withRightToLeft(AppLocale::isRtl())
                ->withShowGridLines(false)
                ->withFreezeRow($headerRow + 1),
        );

        foreach ($columnWidths as $index => $width) {
            $sheet->setColumnWidth((float) $width, $index + 1);
        }

        if ($lastDataRow >= $headerRow) {
            $sheet->setAutoFilter(new AutoFilter(
                0,
                $headerRow,
                max(0, $columnCount - 1),
                $lastDataRow,
            ));
        }
    }

    public static function titleStyle(): Style
    {
        return (new Style)
            ->withFontBold(true)
            ->withFontSize(16)
            ->withFontName('Calibri')
            ->withFontColor(self::BRAND)
            ->withCellAlignment(self::alignment())
            ->withCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }

    public static function metaStyle(): Style
    {
        return (new Style)
            ->withFontSize(11)
            ->withFontName('Calibri')
            ->withFontColor(self::BRAND)
            ->withBackgroundColor(self::META_BG)
            ->withCellAlignment(self::alignment())
            ->withCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->withBorder(self::tableBorder());
    }

    public static function headerStyle(): Style
    {
        return (new Style)
            ->withFontBold(true)
            ->withFontSize(11)
            ->withFontName('Calibri')
            ->withFontColor(Color::WHITE)
            ->withBackgroundColor(self::HEADER_BG)
            ->withCellAlignment(CellAlignment::CENTER)
            ->withCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->withBorder(self::tableBorder(Color::WHITE));
    }

    public static function dataStyle(bool $alternate = false): Style
    {
        $style = (new Style)
            ->withFontSize(11)
            ->withFontName('Calibri')
            ->withFontColor(Color::BLACK)
            ->withCellAlignment(self::alignment())
            ->withCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->withShouldWrapText(true)
            ->withBorder(self::tableBorder());

        return $alternate
            ? $style->withBackgroundColor(self::ALT_ROW_BG)
            : $style;
    }

    private static function alignment(): CellAlignment
    {
        return AppLocale::isRtl() ? CellAlignment::RIGHT : CellAlignment::LEFT;
    }

    private static function tableBorder(string $color = self::BORDER): Border
    {
        return new Border(
            new BorderPart(BorderName::TOP, $color, BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::RIGHT, $color, BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::BOTTOM, $color, BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::LEFT, $color, BorderWidth::THIN, BorderStyle::SOLID),
        );
    }
}
