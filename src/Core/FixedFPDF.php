<?php

namespace App\Core;
use Fawno\FPDF\FawnoFPDF;

class FixedFPDF extends FawnoFPDF
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function toWin1251(?string $text): ?string {
        if ($text === null){
            return null;
        }
        return iconv('UTF-8', 'windows-1251//IGNORE', $text);
    }

    /**
     * Универсальный вывод таблицы с автошириной и переносом текста для FPDF/FawnoFPDF.
     *
     * @param FawnoFPDF $pdf
     * @param string $fontname
     * @param array $headers  Массив заголовков колонок (строки)
     * @param array $fields   Массив ключей для данных (строки)
     * @param array $data     Массив данных (каждая строка — ассоциативный массив)
     * @param int $headerFontSize
     * @param int $cellFontSize
     * @param int $cellHeight
     * @param callable|null $valueFormatter  (опционально) функция для форматирования значения: function($value, $field, $row) { ... }
     */
    public static function printTable($pdf, $fontname, array $headers, array $fields, array $data, int $headerFontSize = 12, int $cellFontSize = 12, int $cellHeight = 6, callable $valueFormatter = null)
    {
        // 1. Считаем максимальную ширину для каждой колонки
        $colWidths = [];
        $pdf->SetFont($fontname, 'B', $headerFontSize);
        foreach ($headers as $i => $header) {
            $colWidths[$i] = $pdf->GetStringWidth(self::toWin1251($header));
        }
        $pdf->SetFont($fontname, '', $cellFontSize);
        foreach ($data as $row) {
            foreach ($fields as $i => $field) {
                $value = $row[$field] ?? '';
                if ($valueFormatter) {
                    $value = $valueFormatter($value, $field, $row);
                }
                $w = $pdf->GetStringWidth(self::toWin1251((string)$value));
                if ($w > $colWidths[$i]) {
                    $colWidths[$i] = $w;
                }
            }
        }
        foreach ($colWidths as &$w) {
            $w += 8; // padding
        }
        unset($w);

        $pageWidth = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $totalWidth = array_sum($colWidths);

        if ($totalWidth > $pageWidth) {
            $scale = $pageWidth / $totalWidth;
            foreach ($colWidths as &$w) {
                $w = $w * $scale;
            }
            unset($w);
        }

        // 2. Выводим заголовки
        $pdf->SetFont($fontname, 'B', $headerFontSize);
        $headerCellHeights = [];
        $headerCellTexts = [];
        foreach ($headers as $i => $header) {
            $txt = self::toWin1251($header);
            $lines = $pdf->NbLines($colWidths[$i], $txt);
            $headerCellHeights[$i] = $lines * ($cellHeight + 2); // +2 для чуть большего межстрочного интервала
            $headerCellTexts[$i] = $txt;
        }
        $headerRowHeight = max($headerCellHeights);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        for ($i = 0; $i < count($headers); $i++) {
            $curX = $pdf->GetX();
            $curY = $pdf->GetY();
            $pdf->MultiCell($colWidths[$i], $cellHeight + 2, $headerCellTexts[$i], 'LTR', 'C', false);
            $cellH = $headerCellHeights[$i];
            if ($cellH < $headerRowHeight) {
                $pdf->SetXY($curX, $curY + $cellH);
                $pdf->Cell($colWidths[$i], $headerRowHeight - $cellH, '', 'LR', 0, 'C', false);
            }
            $pdf->SetXY($curX + $colWidths[$i], $curY);
        }
        // Нарисовать нижнюю границу всей строки заголовка
        $pdf->SetXY($x, $y + $headerRowHeight);
        $pdf->Cell(array_sum($colWidths), 0, '', 'T');
        $pdf->SetX($pdf->lMargin);

        // 3. Выводим строки с переносом (MultiCell)
        $pdf->SetFont($fontname, '', $cellFontSize);
        foreach ($data as $row) {
            $repeatRow = true;
            $repeatCount = 0;
            while ($repeatRow) {
                $repeatRow = false;
                $repeatCount++;
                if ($repeatCount > 2) {
                    break;
                }

                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pageBefore = $pdf->PageNo();

                $cellHeights = [];
                $cellTexts = [];
                foreach ($fields as $i => $field) {
                    $value = $row[$field] ?? '';
                    if ($valueFormatter) {
                        $value = $valueFormatter($value, $field, $row);
                    }
                    $txt = self::toWin1251((string)$value);
                    $lines = $pdf->NbLines($colWidths[$i], $txt);
                    $cellHeights[$i] = $lines * $cellHeight;
                    $cellTexts[$i] = $txt;
                }
                $rowHeight = max($cellHeights);

                // Проверяем, помещается ли строка на страницу
                $bottomY = $pdf->GetY() + $rowHeight;
                $pageHeight = $pdf->h - $pdf->bMargin;
                if ($bottomY > $pageHeight) {
                    $pdf->AddPage("L");
                    $pdf->SetFont($fontname, 'B', $headerFontSize);
                    $headerCellHeights = [];
                    $headerCellTexts = [];
                    foreach ($headers as $i => $header) {
                        $txt = self::toWin1251($header);
                        $lines = $pdf->NbLines($colWidths[$i], $txt);
                        $headerCellHeights[$i] = $lines * ($cellHeight + 2); // +2 для чуть большего межстрочного интервала
                        $headerCellTexts[$i] = $txt;
                    }
                    $headerRowHeight = max($headerCellHeights);

                    $x = $pdf->GetX();
                    $y = $pdf->GetY();
                    for ($i = 0; $i < count($headers); $i++) {
                        $curX = $pdf->GetX();
                        $curY = $pdf->GetY();
                        $pdf->MultiCell($colWidths[$i], $cellHeight + 2, $headerCellTexts[$i], 'LTR', 'C', false);
                        $cellH = $headerCellHeights[$i];
                        if ($cellH < $headerRowHeight) {
                            $pdf->SetXY($curX, $curY + $cellH);
                            $pdf->Cell($colWidths[$i], $headerRowHeight - $cellH, '', 'LR', 0, 'C', false);
                        }
                        $pdf->SetXY($curX + $colWidths[$i], $curY);
                    }
                    // Нарисовать нижнюю границу всей строки заголовка
                    $pdf->SetXY($x, $y + $headerRowHeight);
                    $pdf->Cell(array_sum($colWidths), 0, '', 'T');
                    $pdf->SetX($pdf->lMargin);

                    $pdf->SetFont($fontname, '', $cellFontSize);
                    continue;
                }

                for ($i = 0; $i < count($fields); $i++) {
                    $curX = $pdf->GetX();
                    $curY = $pdf->GetY();
                    $pdf->MultiCell($colWidths[$i], $cellHeight, $cellTexts[$i], 'LR', 'L', false);
                    $cellH = $cellHeights[$i];
                    if ($cellH < $rowHeight) {
                        $pdf->SetXY($curX, $curY + $cellH);
                        $pdf->Cell($colWidths[$i], $rowHeight - $cellH, '', 'LR', 0, 'L', false);
                    }
                    $pdf->SetXY($curX + $colWidths[$i], $curY);

                    if ($pdf->PageNo() != $pageBefore) {
                        $pdf->SetX($pdf->lMargin);
                        $repeatRow = true;
                        break;
                    }
                }

                if (!$repeatRow) {
                    $pdf->SetXY($x, $y + $rowHeight);
                    $pdf->Cell(array_sum($colWidths), 0, '', 'T');
                    $pdf->SetX($pdf->lMargin);
                }
            }
        }
    }

    public function NbLines(float $w, string $txt): int {
        $cw = $this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',(string)$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb)
        {
            $c = $s[$i];
            if($c=="\n")
            {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $cw[$c] ?? 0;
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i = $sep+1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}